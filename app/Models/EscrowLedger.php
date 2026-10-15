<?php

namespace App\Models;

use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowLedger extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_deducted' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'status' => EscrowStatus::class,
            'released_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopeHeld(Builder $query): Builder
    {
        return $query->where('status', EscrowStatus::Held);
    }

    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('status', EscrowStatus::Released);
    }
}
