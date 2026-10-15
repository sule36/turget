<?php

namespace App\Enums;

/**
 * Vendor verification status lifecycle (PRD §8.2, §9.3):
 * Draft → Submitted → Under Review → Need Revision / Verified / Rejected / Suspended
 */
enum VendorVerificationStatus: string
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
                'draft' => 'Draf Pengajuan',
                'submitted' => 'Menunggu Review',
                'under_review' => 'Sedang Diverifikasi',
                'need_revision' => 'Perlu Perbaikan Dokumen',
                'verified' => 'Vendor Terverifikasi',
                'rejected' => 'Pengajuan Ditolak',
                'suspended' => 'Kemitraan Ditangguhkan',
            ],
            'en' => [
                'draft' => 'Draft',
                'submitted' => 'Awaiting Review',
                'under_review' => 'Under Verification',
                'need_revision' => 'Document Revision Needed',
                'verified' => 'Verified Vendor',
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
