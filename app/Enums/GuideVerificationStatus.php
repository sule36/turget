<?php

namespace App\Enums;

/**
 * Guide verification status lifecycle (PRD §7.1):
 * Draft → Submitted → Under Review → Need Revision / Verified / Rejected / Suspended
 */
enum GuideVerificationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedRevision = 'need_revision';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'draft' => 'Draf Onboarding',
                'submitted' => 'Menunggu Review',
                'under_review' => 'Sedang Ditinjau',
                'need_revision' => 'Perlu Revisi',
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak',
                'suspended' => 'Ditangguhkan',
            ],
            'en' => [
                'draft' => 'Onboarding Draft',
                'submitted' => 'Submitted',
                'under_review' => 'Under Review',
                'need_revision' => 'Needs Revision',
                'verified' => 'Verified',
                'rejected' => 'Rejected',
                'suspended' => 'Suspended',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'turget-status-draft',
            self::Submitted, self::UnderReview => 'turget-status-in_review',
            self::NeedRevision => 'turget-status-archived',
            self::Verified => 'turget-status-published',
            self::Rejected, self::Suspended => 'turget-status-archived',
        };
    }
}
