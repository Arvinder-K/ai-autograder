<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'story_id',
        'format_type',
        'title',
        'description',
        'status',
        'version',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeatureItem::class)->orderBy('sort_order');
    }
}
