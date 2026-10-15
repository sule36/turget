<?php

namespace App\Enums;

enum EscrowStatus: string
{
    case Held = 'held';
    case Released = 'released';
    case Refunded = 'refunded';
    case Disputed = 'disputed';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'held' => 'Dana Tertahan di Escrow',
                'released' => 'Dana Telah Dilepas ke Penerima',
                'refunded' => 'Dana Dikembalikan ke Wisatawan',
                'disputed' => 'Dalam Sengketa / Investigasi',
            ],
            'en' => [
                'held' => 'Held in Escrow',
                'released' => 'Released to Beneficiary',
                'refunded' => 'Refunded to Traveler',
                'disputed' => 'Disputed / Under Review',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }
}
