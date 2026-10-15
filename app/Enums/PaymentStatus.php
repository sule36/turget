<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Settlement = 'settlement';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'pending' => 'Menunggu Pembayaran',
                'settlement' => 'Berhasil Dibayar',
                'failed' => 'Pembayaran Gagal',
                'expired' => 'Kedaluwarsa',
                'refunded' => 'Direfund',
            ],
            'en' => [
                'pending' => 'Pending Payment',
                'settlement' => 'Payment Settled',
                'failed' => 'Payment Failed',
                'expired' => 'Expired',
                'refunded' => 'Refunded',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }
}
