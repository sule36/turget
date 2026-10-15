<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\App;

/**
 * Locale-keyed JSON fields: {"id": "...", "en": "..."}.
 * Falls back to the app fallback locale, then to any non-empty value.
 *
 * Models declare `protected array $translatable = [...]` and cast those columns to `array`.
 */
trait HasTranslations
{
    public const LOCALES = ['id', 'en'];

    public function tr(string $field, ?string $locale = null): mixed
    {
        return self::pick($this->getAttribute($field), $locale);
    }

    /** Translate a nested section (e.g. sections.history). */
    public function section(string $key, ?string $locale = null): ?string
    {
        $sections = $this->getAttribute('sections') ?? [];

        return self::pick($sections[$key] ?? null, $locale);
    }

    /** All sections translated into the given locale, empty ones removed. */
    public function translatedSections(?string $locale = null): array
    {
        $out = [];
        foreach ($this->getAttribute('sections') ?? [] as $key => $value) {
            $text = self::pick($value, $locale);
            if (filled($text)) {
                $out[$key] = $text;
            }
        }

        return $out;
    }

    /** Whether a translation exists for the locale (used to flag incomplete translations). */
    public function hasTranslation(string $field, string $locale): bool
    {
        $value = $this->getAttribute($field);

        return is_array($value) && filled($value[$locale] ?? null);
    }

    public static function pick(mixed $value, ?string $locale = null): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $locale ??= App::getLocale();
        $fallback = config('app.fallback_locale', 'en');

        foreach ([$locale, $fallback, 'id', 'en'] as $candidate) {
            if (filled($value[$candidate] ?? null)) {
                return $value[$candidate];
            }
        }

        return null;
    }
}
