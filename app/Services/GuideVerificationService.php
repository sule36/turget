<?php

namespace App\Services;

use App\Enums\GuideVerificationStatus;
use App\Models\AuditLog;
use App\Models\GuideProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class GuideVerificationService
{
    /**
     * Guide submits profile and KYC documents for verification.
     */
    public function submit(GuideProfile $guide): void
    {
        if (! in_array($guide->verification_status, [GuideVerificationStatus::Draft, GuideVerificationStatus::NeedRevision], true)) {
            throw ValidationException::withMessages([
                'status' => 'Hanya profil berstatus draf atau perlu revisi yang dapat diajukan untuk verifikasi.',
            ]);
        }

        $guide->update([
            'verification_status' => GuideVerificationStatus::Submitted,
        ]);

        AuditLog::record('guide.submitted', $guide);
    }

    /**
     * Admin moves guide to Under Review.
     */
    public function startReview(GuideProfile $guide, User $admin): void
    {
        $guide->update([
            'verification_status' => GuideVerificationStatus::UnderReview,
        ]);

        AuditLog::record('guide.under_review', $guide);
    }

    /**
     * Admin approves KYC documents and awards verified badge.
     */
    public function approve(GuideProfile $guide, User $admin, ?string $notes = null): void
    {
        $guide->update([
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'verification_note' => $notes,
        ]);

        // Ensure user has tour_guide role
        if (! $guide->user->hasRole('tour_guide')) {
            $guide->user->assignRole('tour_guide');
        }

        AuditLog::record('guide.verified', $guide);
    }

    /**
     * Admin requests revision with specific notes.
     */
    public function requestRevision(GuideProfile $guide, User $admin, string $notes): void
    {
        $guide->update([
            'verification_status' => GuideVerificationStatus::NeedRevision,
            'verification_note' => $notes,
            'badge_verified' => false,
        ]);

        AuditLog::record('guide.need_revision', $guide);
    }

    /**
     * Admin rejects application.
     */
    public function reject(GuideProfile $guide, User $admin, string $notes): void
    {
        $guide->update([
            'verification_status' => GuideVerificationStatus::Rejected,
            'verification_note' => $notes,
            'badge_verified' => false,
        ]);

        AuditLog::record('guide.rejected', $guide);
    }

    /**
     * Admin suspends an active guide profile.
     */
    public function suspend(GuideProfile $guide, User $admin, string $reason): void
    {
        $guide->update([
            'verification_status' => GuideVerificationStatus::Suspended,
            'badge_verified' => false,
            'verification_note' => $reason,
        ]);

        AuditLog::record('guide.suspended', $guide);
    }

    /**
     * Reactivate a suspended guide.
     */
    public function reactivate(GuideProfile $guide, User $admin): void
    {
        $guide->update([
            'verification_status' => GuideVerificationStatus::Verified,
            'badge_verified' => true,
            'verification_note' => null,
        ]);

        AuditLog::record('guide.reactivated', $guide);
    }
}
