<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideVerification extends Model
{
    protected $guarded = ['id', 'reviewed_at', 'reviewed_by'];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class, 'guide_profile_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
