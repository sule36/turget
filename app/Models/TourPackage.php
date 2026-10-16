<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourPackage extends Model
{
    use HasTranslations, SoftDeletes;

    protected $guarded = ['id', 'approved_at', 'approved_by'];

    protected array $translatable = ['title', 'tagline', 'description', 'inclusions', 'exclusions', 'requirements'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'tagline' => 'array',
            'description' => 'array',
            'itinerary' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'requirements' => 'array',
            'gallery' => 'array',
            'status' => ListingStatus::class,
            'base_price' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class, 'guide_profile_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function vendorAddons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(VendorProduct::class, 'package_vendor_addons')
            ->withPivot(['custom_price', 'is_required', 'notes'])
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published);
    }

    public function publicPath(): string
    {
        return '/packages/'.$this->slug;
    }
}
