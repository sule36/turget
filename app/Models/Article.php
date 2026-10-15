<?php

namespace App\Models;

use App\Models\Concerns\HasContentWorkflow;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasContentWorkflow, HasTranslations, SoftDeletes;

    protected $guarded = ['id', 'status', 'published_at', 'created_by', 'updated_by'];

    protected array $translatable = ['title', 'excerpt', 'body', 'cover_alt'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'excerpt' => 'array',
            'body' => 'array',
            'cover_alt' => 'array',
            'sources' => 'array',
            'is_sponsored' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function countries(): MorphToMany
    {
        return $this->morphedByMany(Country::class, 'articleable');
    }

    public function regions(): MorphToMany
    {
        return $this->morphedByMany(AdministrativeRegion::class, 'articleable');
    }

    public function destinations(): MorphToMany
    {
        return $this->morphedByMany(Destination::class, 'articleable');
    }

    public function publicPath(): string
    {
        return '/articles/'.$this->slug;
    }
}
