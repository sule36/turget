<?php

namespace App\Services;

use App\Enums\VendorVerificationStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Validation\ValidationException;

class VendorVerificationService
{
    /**
     * Vendor submits profile and legal documents for verification.
     */
    public function submit(VendorProfile $vendor): void
    {
        if (! in_array($vendor->verification_status, [VendorVerificationStatus::Draft, VendorVerificationStatus::NeedRevision], true)) {
            throw ValidationException::withMessages([
                'status' => 'Hanya profil draf atau perlu revisi yang dapat diajukan untuk verifikasi.',
            ]);
        }

        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::Submitted,
        ])->save();

        AuditLog::record('vendor.submitted', $vendor);
    }

    /**
     * Admin moves vendor application to Under Review.
     */
    public function startReview(VendorProfile $vendor, User $admin): void
    {
        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::UnderReview,
        ])->save();

        AuditLog::record('vendor.under_review', $vendor);
    }

    /**
     * Admin approves vendor KYC and awards verified badge.
     */
    public function approve(VendorProfile $vendor, User $admin, ?string $notes = null): void
    {
        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'verification_note' => $notes,
        ])->save();

        // Assign 'vendor' role to owner
        if (! $vendor->user->hasRole('vendor')) {
            $vendor->user->assignRole('vendor');
        }

        AuditLog::record('vendor.verified', $vendor);
    }

    /**
     * Admin requests revision from vendor.
     */
    public function requestRevision(VendorProfile $vendor, User $admin, string $notes): void
    {
        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::NeedRevision,
            'badge_verified' => false,
            'verification_note' => $notes,
        ])->save();

        AuditLog::record('vendor.need_revision', $vendor);
    }

    /**
     * Admin rejects application.
     */
    public function reject(VendorProfile $vendor, User $admin, string $notes): void
    {
        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::Rejected,
            'badge_verified' => false,
            'verification_note' => $notes,
        ])->save();

        AuditLog::record('vendor.rejected', $vendor);
    }

    /**
     * Admin suspends vendor partnership.
     */
    public function suspend(VendorProfile $vendor, User $admin, string $reason): void
    {
        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::Suspended,
            'badge_verified' => false,
            'is_active' => false,
            'verification_note' => $reason,
        ])->save();

        AuditLog::record('vendor.suspended', $vendor);
    }

    /**
     * Admin reactivates suspended vendor.
     */
    public function reactivate(VendorProfile $vendor, User $admin): void
    {
        $vendor->forceFill([
            'verification_status' => VendorVerificationStatus::Verified,
            'badge_verified' => true,
            'is_active' => true,
            'verification_note' => null,
        ])->save();

        AuditLog::record('vendor.reactivated', $vendor);
    }
}
