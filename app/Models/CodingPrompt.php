<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodingPrompt extends Model
{
    protected $fillable = [
        'story_id',
        'feature_item_id',
        'technical_design_id',
        'title',
        'prompt_sequence',
        'target_tool',
        'prompt_content',
        'continuation_context',
        'is_continuation',
        'parent_prompt_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['is_continuation' => 'boolean'];
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function featureItem(): BelongsTo
    {
        return $this->belongsTo(FeatureItem::class);
    }

    public function technicalDesign(): BelongsTo
    {
        return $this->belongsTo(TechnicalDesign::class);
    }

    public function parentPrompt(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_prompt_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
