<?php

namespace App\Models;

use App\Enums\GuideVerificationStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuideProfile extends Model
{
    use HasTranslations, SoftDeletes;

    protected $guarded = ['id', 'verified_at', 'verified_by', 'rating_avg', 'reviews_count', 'trips_completed_count'];

    protected array $translatable = ['tagline', 'bio', 'headline', 'cancellation_policy', 'inclusions', 'exclusions'];

    protected function casts(): array
    {
        return [
            'tagline' => 'array',
            'bio' => 'array',
            'headline' => 'array',
            'languages' => 'array',
            'service_areas' => 'array',
            'specializations' => 'array',
            'gallery' => 'array',
            'cancellation_policy' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'verification_status' => GuideVerificationStatus::class,
            'badge_verified' => 'boolean',
            'is_active' => 'boolean',
            'price_per_day' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(AdministrativeRegion::class, 'region_id');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'guide_destinations');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(TourPackage::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(GuideVerification::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(GuideAvailability::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', GuideVerificationStatus::Verified)
            ->where('badge_verified', true)
            ->where('is_active', true);
    }

    public function scopeInRegion(Builder $query, int $regionId): Builder
    {
        return $query->where(function ($q) use ($regionId) {
            $q->where('region_id', $regionId)
                ->orWhereJsonContains('service_areas', $regionId);
        });
    }

    public function publicPath(): string
    {
        return '/guides/'.$this->slug;
    }
}
