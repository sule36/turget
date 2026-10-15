<?php

namespace App\Services;

use App\Enums\EscrowStatus;
use App\Enums\PayoutStatus;
use App\Models\AuditLog;
use App\Models\EscrowLedger;
use App\Models\GuideProfile;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Validation\ValidationException;

class PayoutService
{
    /**
     * Get Guide's balance breakdown (PRD §16.2).
     */
    public function getGuideBalance(GuideProfile $guide): array
    {
        $released = (float) EscrowLedger::where('beneficiary_type', 'guide')
            ->where('beneficiary_id', $guide->id)
            ->where('status', EscrowStatus::Released)
            ->sum('net_amount');

        $heldInEscrow = (float) EscrowLedger::where('beneficiary_type', 'guide')
            ->where('beneficiary_id', $guide->id)
            ->where('status', EscrowStatus::Held)
            ->sum('net_amount');

        $withdrawnOrPending = (float) PayoutRequest::where('guide_profile_id', $guide->id)
            ->whereIn('status', [PayoutStatus::Pending, PayoutStatus::Processing, PayoutStatus::Completed])
            ->sum('amount');

        $available = max(0, $released - $withdrawnOrPending);

        return [
            'available' => $available,
            'held_in_escrow' => $heldInEscrow,
            'total_released' => $released,
            'withdrawn_or_pending' => $withdrawnOrPending,
        ];
    }

    /**
     * Get Vendor's balance breakdown.
     */
    public function getVendorBalance(VendorProfile $vendor): array
    {
        $released = (float) EscrowLedger::where('beneficiary_type', 'vendor')
            ->where('beneficiary_id', $vendor->id)
            ->where('status', EscrowStatus::Released)
            ->sum('net_amount');

        $heldInEscrow = (float) EscrowLedger::where('beneficiary_type', 'vendor')
            ->where('beneficiary_id', $vendor->id)
            ->where('status', EscrowStatus::Held)
            ->sum('net_amount');

        $withdrawnOrPending = (float) PayoutRequest::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', [PayoutStatus::Pending, PayoutStatus::Processing, PayoutStatus::Completed])
            ->sum('amount');

        $available = max(0, $released - $withdrawnOrPending);

        return [
            'available' => $available,
            'held_in_escrow' => $heldInEscrow,
            'total_released' => $released,
            'withdrawn_or_pending' => $withdrawnOrPending,
        ];
    }

    /**
     * Guide or Vendor submits Payout Request.
     */
    public function requestPayout(User $user, float $amount, ?string $bankName = null, ?string $accountNumber = null, ?string $accountHolder = null): PayoutRequest
    {
        $guide = $user->guideProfile;
        $vendor = $user->vendorProfile;

        if (! $guide && ! $vendor) {
            throw ValidationException::withMessages([
                'user' => 'Hanya tour guide atau vendor terdaftar yang dapat mengajukan pencairan dana.',
            ]);
        }

        $balance = $guide ? $this->getGuideBalance($guide) : $this->getVendorBalance($vendor);

        if ($amount <= 0 || $amount > $balance['available']) {
            throw ValidationException::withMessages([
                'amount' => 'Jumlah penarikan melebihi saldo yang tersedia untuk dicairkan.',
            ]);
        }

        $defaultBank = $guide ? $guide->bank_name : $vendor->bank_name;
        $defaultNum = $guide ? $guide->bank_account_number : $vendor->bank_account_number;
        $defaultHolder = $guide ? $guide->bank_account_holder : $vendor->bank_account_holder;

        $payout = PayoutRequest::create([
            'user_id' => $user->id,
            'guide_profile_id' => $guide?->id,
            'vendor_profile_id' => $vendor?->id,
            'amount' => $amount,
            'currency' => 'IDR',
            'bank_name' => $bankName ?? $defaultBank ?? 'BCA',
            'bank_account_number' => $accountNumber ?? $defaultNum ?? '',
            'bank_account_holder' => $accountHolder ?? $defaultHolder ?? $user->name,
            'status' => PayoutStatus::Pending,
        ]);

        AuditLog::record('payout.requested', $payout);

        return $payout;
    }

    /**
     * Admin approves payout and records disbursement.
     */
    public function approvePayout(PayoutRequest $request, User $admin, ?string $proofFilePath = null, ?string $notes = null): void
    {
        $request->update([
            'status' => PayoutStatus::Completed,
            'proof_file_path' => $proofFilePath,
            'admin_notes' => $notes ?? 'Pencairan dana telah ditransfer ke rekening bank penerima.',
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        AuditLog::record('payout.completed', $request);
    }

    /**
     * Admin rejects payout.
     */
    public function rejectPayout(PayoutRequest $request, User $admin, string $reason): void
    {
        $request->update([
            'status' => PayoutStatus::Rejected,
            'admin_notes' => $reason,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        AuditLog::record('payout.rejected', $request);
    }
}
