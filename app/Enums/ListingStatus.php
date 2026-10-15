<?php

namespace App\Enums;

/**
 * Tour package / listing status lifecycle (PRD §7.5):
 * Draft → Submitted → Under Review → Approved → Published → Paused / Rejected
 */
enum ListingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Published = 'published';
    case Paused = 'paused';
    case Archived = 'archived';
    case Rejected = 'rejected';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'draft' => 'Draf Paket',
                'submitted' => 'Diajukan',
                'under_review' => 'Sedang Dimoderasi',
                'approved' => 'Disetujui',
                'published' => 'Aktif / Tayang',
                'paused' => 'Dijeda',
                'archived' => 'Diarsipkan',
                'rejected' => 'Ditolak',
            ],
            'en' => [
                'draft' => 'Draft Listing',
                'submitted' => 'Submitted',
                'under_review' => 'Under Moderation',
                'approved' => 'Approved',
                'published' => 'Active / Published',
                'paused' => 'Paused',
                'archived' => 'Archived',
                'rejected' => 'Rejected',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }
}
