<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugRedirect extends Model
{
    protected $guarded = ['id'];

    public static function findMatch(string $path): ?self
    {
        $normalized = '/' . ltrim($path, '/');
        $redirect = self::where('from_path', $normalized)->first();

        if ($redirect) {
            $redirect->increment('hits');
        }

        return $redirect;
    }
}
