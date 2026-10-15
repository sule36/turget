<?php

namespace App\Models;

use App\Enums\TripInquiryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripInquiry extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'duration_days' => 'integer',
            'adults_count' => 'integer',
            'children_count' => 'integer',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'preferences' => 'array',
            'status' => TripInquiryStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(AdministrativeRegion::class, 'region_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(TripProposal::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [TripInquiryStatus::Open, TripInquiryStatus::Bidding]);
    }
}
