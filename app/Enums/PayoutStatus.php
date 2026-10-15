<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'pending' => 'Menunggu Persetujuan Admin',
                'processing' => 'Sedang Ditransfer Bank',
                'completed' => 'Berhasil Dicairkan',
                'rejected' => 'Permintaan Ditolak',
            ],
            'en' => [
                'pending' => 'Pending Admin Review',
                'processing' => 'Bank Transfer in Progress',
                'completed' => 'Disbursed / Completed',
                'rejected' => 'Rejected',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'turget-status-draft',
            self::Processing => 'turget-status-in_review',
            self::Completed => 'turget-status-published',
            self::Rejected => 'turget-status-archived',
        };
    }
}
