<?php

namespace App\Models;

use App\Enums\VendorCategory;
use App\Enums\VendorVerificationStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorProfile extends Model
{
    use HasTranslations, SoftDeletes;

    protected $guarded = [
        'id',
        'verification_status',
        'verified_at',
        'verified_by',
        'badge_verified',
        'rating_avg',
        'reviews_count',
        'bookings_completed_count',
    ];

    protected array $translatable = ['description', 'cancellation_policy', 'opening_hours'];

    protected function casts(): array
    {
        return [
            'service_areas' => 'array',
            'gallery' => 'array',
            'description' => 'array',
            'cancellation_policy' => 'array',
            'opening_hours' => 'array',
            'category' => VendorCategory::class,
            'verification_status' => VendorVerificationStatus::class,
            'badge_verified' => 'boolean',
            'is_active' => 'boolean',
            'rating_avg' => 'decimal:2',
            'latitude' => 'float',
            'longitude' => 'float',
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

    public function products(): HasMany
    {
        return $this->hasMany(VendorProduct::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(VendorVerification::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', VendorVerificationStatus::Verified->value)
            ->where('badge_verified', true)
            ->where('is_active', true);
    }

    public function scopeCategory(Builder $query, string|VendorCategory $category): Builder
    {
        $val = $category instanceof VendorCategory ? $category->value : $category;

        return $query->where('category', $val);
    }

    /**
     * Great-circle distance search using spherical law of cosines / Haversine formula (PRD §10.2).
     */
    public function scopeNear(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $haversine = '(6371 * acos(least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))';

        return $query->whereNotNull('latitude')->whereNotNull('longitude')
            ->select('vendor_profiles.*')
            ->selectRaw("$haversine as distance_km", [$lat, $lng, $lat])
            ->whereRaw("$haversine <= ?", [$lat, $lng, $lat, $radiusKm])
            ->orderBy('distance_km');
    }

    public function publicPath(): string
    {
        return '/vendors/'.$this->slug;
    }
}
