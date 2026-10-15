<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected array $translatable = ['alt', 'caption'];

    protected function casts(): array
    {
        return [
            'alt' => 'array',
            'caption' => 'array',
        ];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
