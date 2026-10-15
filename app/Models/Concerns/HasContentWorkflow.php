<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use App\Models\ContentRevision;
use App\Models\SlugRedirect;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Shared behaviour for publishable CMS content (countries, regions, destinations, articles).
 */
trait HasContentWorkflow
{
    abstract public function publicPath(): string;

    public static function bootHasContentWorkflow(): void
    {
        // PRD §6.3: keep old URLs working when a slug (or a parent slug) changes.
        static::updating(function ($model) {
            if (! $model->isDirty('slug') || $model->getRawOriginal('status') !== ContentStatus::Published->value) {
                return;
            }

            $old = (clone $model)->setRawAttributes($model->getRawOriginal());
            $from = $old->publicPath();
            $to = $model->publicPath();

            if ($from !== $to) {
                SlugRedirect::updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301]);
                // Avoid redirect loops if an older redirect pointed back at the new path.
                SlugRedirect::where('from_path', $to)->delete();
            }
        });
    }

    public function initializeHasContentWorkflow(): void
    {
        $this->mergeCasts([
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'facts_verified_at' => 'datetime',
            'is_featured' => 'boolean',
            'sources' => 'array',
            'meta' => 'array',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where($this->getTable().'.status', ContentStatus::Published->value);
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisionable')->latest('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::Published;
    }

    public function metaTitle(?string $locale = null): ?string
    {
        return HasTranslations::pick($this->meta['title'] ?? null, $locale);
    }

    public function metaDescription(?string $locale = null): ?string
    {
        return HasTranslations::pick($this->meta['description'] ?? null, $locale);
    }
}
