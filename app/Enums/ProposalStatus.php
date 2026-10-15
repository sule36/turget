<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Submitted = 'submitted';
    case Shortlisted = 'shortlisted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'submitted' => 'Menunggu Keputusan',
                'shortlisted' => 'Disukai Traveler',
                'accepted' => 'Diterima & Siap Bayar',
                'rejected' => 'Tidak Dipilih',
                'expired' => 'Kedaluwarsa',
            ],
            'en' => [
                'submitted' => 'Submitted',
                'shortlisted' => 'Shortlisted',
                'accepted' => 'Accepted',
                'rejected' => 'Declined',
                'expired' => 'Expired',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'turget-status-in_review',
            self::Shortlisted => 'turget-status-in_review',
            self::Accepted => 'turget-status-published',
            self::Rejected, self::Expired => 'turget-status-archived',
        };
    }
}
