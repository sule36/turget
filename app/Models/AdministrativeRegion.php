<?php

namespace App\Models;

use App\Models\Concerns\HasContentWorkflow;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Flexible administrative hierarchy (PRD §4.2, §23.2).
 * Level 1 = province/state/region, level 2 = regency/city. Deeper levels are supported by the schema.
 */
class AdministrativeRegion extends Model
{
    use HasContentWorkflow, HasTranslations, SoftDeletes;

    public const TYPES = ['province', 'state', 'region', 'special_region', 'regency', 'city', 'district'];

    protected $guarded = ['id', 'status', 'published_at', 'created_by', 'updated_by'];

    protected array $translatable = ['name', 'tagline', 'summary', 'cover_alt'];

    protected function casts(): array
    {
        return [
            'name' => 'array', 'tagline' => 'array', 'summary' => 'array', 'sections' => 'array',
            'facts' => 'array', 'cover_alt' => 'array',
            'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'level' => 'integer',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class, 'region_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany(Article::class, 'articleable');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeCityLevel(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function isCityLevel(): bool
    {
        return $this->parent_id !== null;
    }

    /** Ancestors from the top level down to (and excluding) this region. */
    public function ancestors(): array
    {
        $chain = [];
        $node = $this->parent;
        while ($node) {
            array_unshift($chain, $node);
            $node = $node->parent;
        }

        return $chain;
    }

    public function publicPath(): string
    {
        $segments = array_map(fn ($r) => $r->slug, $this->ancestors());
        $segments[] = $this->slug;

        return '/destinations/'.$this->country->slug.'/'.implode('/', $segments);
    }

    public function typeLabel(?string $locale = null): string
    {
        $labels = [
            'id' => ['province' => 'Provinsi', 'state' => 'Negara bagian', 'region' => 'Wilayah', 'special_region' => 'Daerah Istimewa',
                'regency' => 'Kabupaten', 'city' => 'Kota', 'district' => 'Kecamatan'],
            'en' => ['province' => 'Province', 'state' => 'State', 'region' => 'Region', 'special_region' => 'Special Region',
                'regency' => 'Regency', 'city' => 'City', 'district' => 'District'],
        ];
        $locale ??= app()->getLocale();

        return $labels[$locale][$this->type] ?? ucfirst($this->type);
    }
}
