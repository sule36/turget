<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripProposal extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'itinerary' => 'array',
            'price_breakdown' => 'array',
            'total_amount' => 'decimal:2',
            'valid_until' => 'date',
            'status' => ProposalStatus::class,
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(TripInquiry::class, 'trip_inquiry_id');
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class, 'guide_profile_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [ProposalStatus::Submitted, ProposalStatus::Shortlisted, ProposalStatus::Accepted]);
    }
}
