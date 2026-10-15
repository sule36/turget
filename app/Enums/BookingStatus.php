<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaidEscrow = 'paid_escrow';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'pending_payment' => 'Menunggu Pembayaran',
                'paid_escrow' => 'Dibayar (Dana Aman di Escrow)',
                'confirmed' => 'Terkonfirmasi & Siap Trip',
                'in_progress' => 'Trip Sedang Berjalan',
                'completed' => 'Trip Selesai & Dana Dilepas',
                'cancelled' => 'Pesanan Dibatalkan',
                'refunded' => 'Dana Telah Direfund',
            ],
            'en' => [
                'pending_payment' => 'Pending Payment',
                'paid_escrow' => 'Paid (Protected in Escrow)',
                'confirmed' => 'Confirmed & Ready',
                'in_progress' => 'Trip In Progress',
                'completed' => 'Completed & Released',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingPayment => 'turget-status-draft',
            self::PaidEscrow, self::Confirmed, self::InProgress => 'turget-status-in_review',
            self::Completed => 'turget-status-published',
            self::Cancelled, self::Refunded => 'turget-status-archived',
        };
    }
}
