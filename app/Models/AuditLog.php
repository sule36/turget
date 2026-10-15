<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $action, ?Model $target = null, array $metadata = [], ?Request $request = null): self
    {
        $request ??= request();

        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $target ? get_class($target) : null,
            'auditable_id' => $target?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
