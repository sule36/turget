<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve target entity (GuideProfile, TourPackage, Destination).
     */
    public function getTargetAttribute(): ?Model
    {
        return match ($this->favoritable_type) {
            'guide' => GuideProfile::with(['region', 'country'])->find($this->favoritable_id),
            'package' => TourPackage::with(['guide', 'destination'])->find($this->favoritable_id),
            'destination' => Destination::with(['region'])->find($this->favoritable_id),
            default => null,
        };
    }
}
