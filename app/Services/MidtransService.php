<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct(
        protected PaymentAndEscrowService $paymentService
    ) {}

    public function getServerKey(): string
    {
        return (string) config('services.midtrans.server_key', '');
    }

    public function getClientKey(): string
    {
        return (string) config('services.midtrans.client_key', '');
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production', false);
    }

    public function getSnapBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Generate Midtrans Snap token for a pending booking.
     */
    public function createSnapToken(Booking $booking): ?array
    {
        $serverKey = $this->getServerKey();
        if (empty($serverKey)) {
            return null;
        }

        $grossAmount = (int) round((float) $booking->total_amount);

        $params = [
            'transaction_details' => [
                'order_id' => $booking->booking_code . '-' . time(),
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $booking->contact_name ?: ($booking->traveler?->name ?? 'Wisatawan'),
                'email' => $booking->contact_email ?: ($booking->traveler?->email ?? 'traveler@turget.id'),
                'phone' => $booking->contact_phone ?: ($booking->traveler?->phone ?? '081234567890'),
            ],
            'item_details' => [
                [
                    'id' => 'BOOKING-' . $booking->id,
                    'price' => (int) round((float) $booking->subtotal),
                    'quantity' => 1,
                    'name' => mb_substr($booking->title ?: 'Layanan Wisata Turget.id', 0, 50),
                ],
                [
                    'id' => 'FEE-ESCROW',
                    'price' => (int) round((float) $booking->platform_fee),
                    'quantity' => 1,
                    'name' => 'Biaya Layanan & Rekening Bersama Escrow',
                ],
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getSnapBaseUrl(), $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ];
            }

            Log::error('Midtrans Snap creation error: ' . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify and process incoming Midtrans Webhook Notification.
     */
    public function handleNotification(array $payload): array
    {
        $serverKey = $this->getServerKey();
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        // 1. Signature Verification
        if (! empty($serverKey)) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if ($signature !== $expectedSignature) {
                Log::warning("Midtrans signature mismatch for order {$orderId}");
                return ['status' => 'error', 'message' => 'Invalid signature'];
            }
        }

        // Extract base booking code (before timestamp suffix if added)
        $bookingCode = explode('-', $orderId);
        if (count($bookingCode) >= 4) {
            // E.g., TRG-PKG-XXXXXX-1729000000 -> TRG-PKG-XXXXXX
            $realCode = implode('-', array_slice($bookingCode, 0, 3));
        } else {
            $realCode = $orderId;
        }

        $booking = Booking::where('booking_code', $realCode)->first();
        if (! $booking) {
            Log::warning("Midtrans webhook: Booking not found for code {$realCode}");
            return ['status' => 'error', 'message' => 'Booking not found'];
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $paymentType = $payload['payment_type'] ?? 'midtrans';
        $transactionId = $payload['transaction_id'] ?? null;

        Log::info("Midtrans Webhook: {$realCode} status {$transactionStatus} fraud {$fraudStatus}");

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept' && $booking->status === BookingStatus::PendingPayment) {
                $this->paymentService->processPaymentSuccess($booking, 'midtrans', $paymentType, $transactionId);
            }
        } elseif ($transactionStatus === 'settlement') {
            if ($booking->status === BookingStatus::PendingPayment) {
                $this->paymentService->processPaymentSuccess($booking, 'midtrans', $paymentType, $transactionId);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            if ($booking->status === BookingStatus::PendingPayment) {
                $booking->update(['status' => BookingStatus::Cancelled]);
            }
        }

        return ['status' => 'success'];
    }
}
