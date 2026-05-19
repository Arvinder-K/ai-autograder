<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryVersion extends Model
{
    protected $fillable = [
        'story_id',
        'version_number',
        'title',
        'snapshot_data',
        'change_summary',
        'changed_by',
    ];

    protected function casts(): array
    {
        return ['snapshot_data' => 'array'];
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
