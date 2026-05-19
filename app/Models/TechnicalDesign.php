<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalDesign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'story_id',
        'feature_list_id',
        'title',
        'system_architecture',
        'design_details',
        'presentation_content',
        'audience',
        'status',
        'created_by',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function featureList(): BelongsTo
    {
        return $this->belongsTo(FeatureList::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
