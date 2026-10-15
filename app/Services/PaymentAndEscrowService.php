<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EscrowStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProposalStatus;
use App\Enums\TripInquiryStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\EscrowLedger;
use App\Models\RefundRequest;
use App\Models\TourPackage;
use App\Models\Transaction;
use App\Models\TripProposal;
use App\Models\User;
use App\Models\VendorProduct;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentAndEscrowService
{
    public const PLATFORM_COMMISSION_PERCENT = 0.10; // 10% Turget platform fee (PRD §15.2)

    /**
     * Create Booking from Fixed Tour Package with optional Vendor Add-ons (PRD §8.2, §14).
     */
    public function createBookingFromPackage(TourPackage $package, User $traveler, array $data): Booking
    {
        $participants = (int) ($data['participants_count'] ?? 1);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'] ?? $startDate;

        $packageSubtotal = (float) $package->base_price * $participants;
        $addonsSubtotal = 0.0;

        $bookingCode = 'TRG-PKG-'.strtoupper(Str::random(6));

        $booking = new Booking();
        $booking->booking_code = $bookingCode;
        $booking->traveler_id = $traveler->id;
        $booking->guide_profile_id = $package->guide_profile_id;
        $booking->tour_package_id = $package->id;
        $booking->booking_type = 'package';
        $booking->start_date = $startDate;
        $booking->end_date = $endDate;
        $booking->participants_count = $participants;
        $booking->currency = 'IDR';
        $booking->status = BookingStatus::PendingPayment;
        $booking->cancellation_policy = $package->guide?->cancellation_policy ?? [
            'id' => 'Pembatalan gratis hingga 48 jam sebelum jadwal trip.',
            'en' => 'Free cancellation up to 48 hours before trip date.',
        ];
        $booking->special_notes = $data['special_notes'] ?? null;
        $booking->payment_deadline = now()->addHours(24);
        $booking->subtotal = 0; // will update after addons
        $booking->platform_fee = 0;
        $booking->total_amount = 0;
        $booking->save();

        // Process Add-ons
        if (! empty($data['addons']) && is_array($data['addons'])) {
            foreach ($data['addons'] as $addonItem) {
                $vendorProduct = VendorProduct::find($addonItem['vendor_product_id']);
                if ($vendorProduct) {
                    $qty = (int) ($addonItem['quantity'] ?? 1);
                    $unitPrice = (float) ($addonItem['custom_price'] ?? $vendorProduct->price);
                    $totalAddonPrice = $unitPrice * $qty;
                    $addonsSubtotal += $totalAddonPrice;

                    BookingAddon::create([
                        'booking_id' => $booking->id,
                        'vendor_product_id' => $vendorProduct->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalAddonPrice,
                    ]);
                }
            }
        }

        $subtotal = $packageSubtotal + $addonsSubtotal;
        $platformFee = round($subtotal * self::PLATFORM_COMMISSION_PERCENT, 2);
        $totalAmount = $subtotal + $platformFee;

        $booking->update([
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'total_amount' => $totalAmount,
        ]);

        AuditLog::record('booking.created', $booking);

        return $booking;
    }

    /**
     * Create Booking directly from Vendor Product (Stay, Car Rental, Boat, Activity).
     */
    public function createBookingFromVendorProduct(VendorProduct $product, User $traveler, array $data): Booking
    {
        $quantity = (int) ($data['quantity'] ?? 1);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'] ?? $startDate;

        $subtotal = (float) $product->price * $quantity;
        $platformFee = round($subtotal * self::PLATFORM_COMMISSION_PERCENT, 2);
        $totalAmount = $subtotal + $platformFee;

        $bookingCode = 'TRG-VND-'.strtoupper(Str::random(6));

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'traveler_id' => $traveler->id,
            'vendor_profile_id' => $product->vendor_profile_id,
            'vendor_product_id' => $product->id,
            'booking_type' => 'vendor_product',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'participants_count' => (int) ($data['participants_count'] ?? 1),
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'total_amount' => $totalAmount,
            'currency' => 'IDR',
            'status' => BookingStatus::PendingPayment,
            'cancellation_policy' => $product->vendor?->cancellation_policy ?? [
                'id' => 'Pembatalan gratis hingga 48 jam sebelum jadwal layanan.',
            ],
            'special_notes' => $data['special_notes'] ?? null,
            'payment_deadline' => now()->addHours(24),
        ]);

        AuditLog::record('booking.created_vendor', $booking);

        return $booking;
    }

    /**
     * Create Booking from Accepted Custom Trip Proposal (PRD §12, §14).
     */
    public function createBookingFromProposal(TripProposal $proposal, User $traveler): Booking
    {
        $inquiry = $proposal->inquiry;
        $subtotal = (float) $proposal->total_amount;
        $platformFee = round($subtotal * self::PLATFORM_COMMISSION_PERCENT, 2);
        $totalAmount = $subtotal + $platformFee;

        $bookingCode = 'TRG-TRP-'.strtoupper(Str::random(6));

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $proposal->guide_profile_id,
            'trip_proposal_id' => $proposal->id,
            'booking_type' => 'custom_trip',
            'start_date' => $inquiry->start_date,
            'end_date' => $inquiry->end_date,
            'participants_count' => $inquiry->adults_count + $inquiry->children_count,
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'total_amount' => $totalAmount,
            'currency' => 'IDR',
            'status' => BookingStatus::PendingPayment,
            'cancellation_policy' => [
                'id' => 'Pembatalan paket custom trip tunduk pada pengembalian dana bertahap sesuai kebijakan HPI.',
            ],
            'special_notes' => $inquiry->special_requests,
            'payment_deadline' => now()->addHours(24),
        ]);

        // Update proposal and inquiry status
        $proposal->update(['status' => ProposalStatus::Accepted]);
        $inquiry->update(['status' => TripInquiryStatus::Booked]);

        AuditLog::record('booking.created_custom_trip', $booking);

        return $booking;
    }

    /**
     * Process Successful Payment & Lock Funds into Escrow Ledger (PRD §15, §16.1).
     */
    public function processPaymentSuccess(
        Booking $booking,
        string $gateway = 'sandbox',
        string $method = 'qris',
        ?string $reference = null
    ): Transaction {
        if ($booking->status !== BookingStatus::PendingPayment) {
            throw ValidationException::withMessages([
                'booking' => 'Status pesanan ini tidak dalam posisi menunggu pembayaran.',
            ]);
        }

        $txCode = 'TX-'.strtoupper(Str::random(8));

        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'transaction_code' => $txCode,
            'payment_gateway' => $gateway,
            'payment_method' => $method,
            'gateway_reference' => $reference ?? 'SANDBOX-'.now()->timestamp,
            'amount' => $booking->total_amount,
            'platform_fee' => $booking->platform_fee,
            'status' => PaymentStatus::Settlement,
            'paid_at' => now(),
            'payload' => [
                'gateway' => $gateway,
                'method' => $method,
                'paid_at' => now()->toIso8601String(),
            ],
        ]);

        $booking->update([
            'status' => BookingStatus::PaidEscrow,
            'paid_at' => now(),
        ]);

        // 1. Escrow Ledger: Guide Beneficiary
        if ($booking->guide_profile_id) {
            // If custom trip or package
            $guideNet = $booking->subtotal;
            // Subtract any vendor addons if present
            if ($booking->addons()->exists()) {
                $addonsTotal = (float) $booking->addons()->sum('total_price');
                $guideNet = max(0, $booking->subtotal - $addonsTotal);
            }

            EscrowLedger::create([
                'booking_id' => $booking->id,
                'transaction_id' => $transaction->id,
                'beneficiary_type' => 'guide',
                'beneficiary_id' => $booking->guide_profile_id,
                'amount' => $guideNet,
                'fee_deducted' => 0,
                'net_amount' => $guideNet,
                'status' => EscrowStatus::Held,
                'notes' => "Dana honor pandu wisata untuk pesanan {$booking->booking_code}",
            ]);
        }

        // 2. Escrow Ledger: Vendor Add-ons
        if ($booking->addons()->exists()) {
            foreach ($booking->addons as $addon) {
                EscrowLedger::create([
                    'booking_id' => $booking->id,
                    'transaction_id' => $transaction->id,
                    'beneficiary_type' => 'vendor',
                    'beneficiary_id' => $addon->vendorProduct?->vendor_profile_id,
                    'amount' => $addon->total_price,
                    'fee_deducted' => 0,
                    'net_amount' => $addon->total_price,
                    'status' => EscrowStatus::Held,
                    'notes' => "Dana add-on layanan vendor ({$addon->vendorProduct?->name['id']})",
                ]);
            }
        }

        // 3. Escrow Ledger: Direct Vendor Product booking
        if ($booking->vendor_profile_id && ! $booking->guide_profile_id) {
            EscrowLedger::create([
                'booking_id' => $booking->id,
                'transaction_id' => $transaction->id,
                'beneficiary_type' => 'vendor',
                'beneficiary_id' => $booking->vendor_profile_id,
                'amount' => $booking->subtotal,
                'fee_deducted' => 0,
                'net_amount' => $booking->subtotal,
                'status' => EscrowStatus::Held,
                'notes' => "Dana sewa/layanan vendor untuk pesanan {$booking->booking_code}",
            ]);
        }

        // 4. Escrow Ledger: Turget Platform 10% Fee
        EscrowLedger::create([
            'booking_id' => $booking->id,
            'transaction_id' => $transaction->id,
            'beneficiary_type' => 'platform',
            'beneficiary_id' => null,
            'amount' => $booking->platform_fee,
            'fee_deducted' => 0,
            'net_amount' => $booking->platform_fee,
            'status' => EscrowStatus::Held,
            'notes' => "Platform commission Turget.id 10% ({$booking->booking_code})",
        ]);

        AuditLog::record('transaction.paid_escrow', $transaction);

        return $transaction;
    }

    /**
     * Traveler confirms trip completion -> Escrow funds released to Guide & Vendors (PRD §15.2, §16.1).
     */
    public function releaseEscrowOnCompletion(Booking $booking, User $actor): void
    {
        if (! in_array($booking->status, [BookingStatus::PaidEscrow, BookingStatus::Confirmed, BookingStatus::InProgress], true)) {
            throw ValidationException::withMessages([
                'booking' => 'Hanya pesanan yang telah dibayar yang dapat diselesaikan.',
            ]);
        }

        $booking->update([
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);

        // Release all held ledgers
        EscrowLedger::where('booking_id', $booking->id)
            ->where('status', EscrowStatus::Held)
            ->update([
                'status' => EscrowStatus::Released,
                'released_at' => now(),
            ]);

        // Increment stats on guide/vendor
        if ($booking->guide) {
            $booking->guide->increment('trips_completed_count');
        }
        if ($booking->vendor) {
            $booking->vendor->increment('bookings_completed_count');
        }

        AuditLog::record('booking.completed_released', $booking, ['released_by' => $actor->id]);
    }

    /**
     * Process Traveler Refund according to cancellation policy (PRD §17).
     */
    public function processRefund(Booking $booking, string $reason, ?User $admin = null): RefundRequest
    {
        if (! in_array($booking->status, [BookingStatus::PaidEscrow, BookingStatus::Confirmed], true)) {
            throw ValidationException::withMessages([
                'booking' => 'Pesanan ini tidak memenuhi syarat untuk pengajuan refund.',
            ]);
        }

        // Calculate refund percentage based on time remaining until start_date
        $tripStart = \Illuminate\Support\Carbon::parse($booking->start_date)->startOfDay();
        $hoursUntilTrip = now()->diffInHours($tripStart, false);

        $requestedAmount = $booking->subtotal;
        $approvedAmount = 0.0;
        $deductionFee = 0.0;

        if ($hoursUntilTrip >= 48) {
            // Full refund minus platform administrative gateway fee (5%)
            $deductionFee = round($requestedAmount * 0.05, 2);
            $approvedAmount = $requestedAmount - $deductionFee;
        } elseif ($hoursUntilTrip >= 24) {
            // 50% partial refund
            $deductionFee = round($requestedAmount * 0.50, 2);
            $approvedAmount = $requestedAmount - $deductionFee;
        } else {
            // < 24 hours: Non-refundable (guide already mobilized)
            $deductionFee = $requestedAmount;
            $approvedAmount = 0.0;
        }

        $refundRequest = RefundRequest::create([
            'booking_id' => $booking->id,
            'traveler_id' => $booking->traveler_id,
            'reason' => $reason,
            'requested_amount' => $requestedAmount,
            'approved_amount' => $approvedAmount,
            'deduction_fee' => $deductionFee,
            'status' => 'approved',
            'admin_notes' => "Refund otomatis berdasarkan kebijakan pembatalan ({$hoursUntilTrip} jam sebelum keberangkatan).",
            'processed_by' => $admin?->id,
            'processed_at' => now(),
        ]);

        $booking->update([
            'status' => BookingStatus::Refunded,
        ]);

        // Mark escrow ledgers as refunded
        EscrowLedger::where('booking_id', $booking->id)
            ->where('status', EscrowStatus::Held)
            ->update([
                'status' => EscrowStatus::Refunded,
            ]);

        AuditLog::record('booking.refunded', $refundRequest);

        return $refundRequest;
    }
}
