<?php

namespace App\Enums;

enum TripInquiryStatus: string
{
    case Open = 'open';
    case Bidding = 'bidding';
    case Accepted = 'accepted';
    case Booked = 'booked';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'open' => 'Terbuka untuk Guide',
                'bidding' => 'Menerima Penawaran',
                'accepted' => 'Proposal Diterima',
                'booked' => 'Telah Dipesan',
                'completed' => 'Trip Selesai',
                'cancelled' => 'Dibatalkan',
            ],
            'en' => [
                'open' => 'Open for Bidding',
                'bidding' => 'Receiving Proposals',
                'accepted' => 'Proposal Accepted',
                'booked' => 'Booked',
                'completed' => 'Trip Completed',
                'cancelled' => 'Cancelled',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'turget-status-in_review',
            self::Bidding => 'turget-status-in_review',
            self::Accepted, self::Booked => 'turget-status-published',
            self::Completed => 'turget-status-published',
            self::Cancelled => 'turget-status-archived',
        };
    }
}
