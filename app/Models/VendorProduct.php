<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\VendorCategory;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorProduct extends Model
{
    use HasTranslations, SoftDeletes;

    protected $guarded = [
        'id',
        'status',
        'moderation_note',
        'approved_at',
        'approved_by',
        'rating_avg',
        'reviews_count',
    ];

    protected array $translatable = ['name', 'description', 'inclusions', 'exclusions', 'terms'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'terms' => 'array',
            'gallery' => 'array',
            'category' => VendorCategory::class,
            'status' => ListingStatus::class,
            'price' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'is_featured' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'approved_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function tourPackages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'package_vendor_addons')
            ->withPivot(['custom_price', 'is_required', 'notes'])
            ->withTimestamps();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published->value)
            ->whereHas('vendor', function (Builder $q) {
                $q->where('is_active', true);
            });
    }

    public function scopeCategory(Builder $query, string|VendorCategory $category): Builder
    {
        $val = $category instanceof VendorCategory ? $category->value : $category;

        return $query->where('category', $val);
    }

    /**
     * Haversine search around coordinates.
     */
    public function scopeNear(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $haversine = '(6371 * acos(least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))';

        return $query->whereNotNull('latitude')->whereNotNull('longitude')
            ->select('vendor_products.*')
            ->selectRaw("$haversine as distance_km", [$lat, $lng, $lat])
            ->whereRaw("$haversine <= ?", [$lat, $lng, $lat, $radiusKm])
            ->orderBy('distance_km');
    }

    public function publicPath(): string
    {
        return '/vendors/products/'.$this->slug;
    }
}
