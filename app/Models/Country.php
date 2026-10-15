<?php

namespace App\Models;

use App\Models\Concerns\HasContentWorkflow;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasContentWorkflow, HasTranslations, SoftDeletes;

    protected $guarded = ['id', 'status', 'published_at', 'created_by', 'updated_by'];

    protected array $translatable = ['name', 'tagline', 'summary', 'cover_alt'];

    protected function casts(): array
    {
        return [
            'name' => 'array', 'tagline' => 'array', 'summary' => 'array', 'sections' => 'array',
            'facts' => 'array', 'cover_alt' => 'array',
        ];
    }

    public function regions(): HasMany
    {
        return $this->hasMany(AdministrativeRegion::class);
    }

    /** Top-level administrative regions (province / state / region). */
    public function topRegions(): HasMany
    {
        return $this->regions()->whereNull('parent_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany(Article::class, 'articleable');
    }

    public function publicPath(): string
    {
        return '/destinations/'.$this->slug;
    }
}
