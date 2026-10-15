<?php

namespace App\Models;

use App\Models\Concerns\HasContentWorkflow;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{
    use HasContentWorkflow, HasTranslations, SoftDeletes;

    protected $guarded = ['id', 'status', 'published_at', 'created_by', 'updated_by', 'popularity_score'];

    protected array $translatable = ['name', 'tagline', 'summary', 'cover_alt', 'opening_hours', 'ticket_info'];

    protected function casts(): array
    {
        return [
            'name' => 'array', 'tagline' => 'array', 'summary' => 'array', 'sections' => 'array',
            'facts' => 'array', 'cover_alt' => 'array', 'highlights' => 'array', 'activities' => 'array',
            'opening_hours' => 'array', 'ticket_info' => 'array',
            'latitude' => 'decimal:7', 'longitude' => 'decimal:7',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** The city/regency-level region this destination belongs to. */
    public function region(): BelongsTo
    {
        return $this->belongsTo(AdministrativeRegion::class, 'region_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(DestinationCategory::class, 'destination_category_destination');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany(Article::class, 'articleable');
    }

    public function hasValidCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null
            && abs((float) $this->latitude) <= 90 && abs((float) $this->longitude) <= 180
            && ! ((float) $this->latitude === 0.0 && (float) $this->longitude === 0.0);
    }

    /**
     * Great-circle distance filter/sort (PRD §10.2). Only rows with coordinates are considered,
     * so nothing is labelled "nearby" from an unverifiable location.
     */
    public function scopeNear(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $haversine = '(6371 * acos(least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))';

        return $query->whereNotNull('latitude')->whereNotNull('longitude')
            ->select('destinations.*')
            ->selectRaw("$haversine as distance_km", [$lat, $lng, $lat])
            ->whereRaw("$haversine <= ?", [$lat, $lng, $lat, $radiusKm])
            ->orderBy('distance_km');
    }

    /** {id:[..], en:[..]} → list for locale. */
    public function listFor(string $field, ?string $locale = null): array
    {
        return array_values(array_filter((array) (self::pick($this->getAttribute($field), $locale) ?? [])));
    }

    public function publicPath(): string
    {
        return '/places/'.$this->slug;
    }
}
