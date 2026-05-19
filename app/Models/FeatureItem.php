<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureItem extends Model
{
    protected $fillable = [
        'feature_list_id',
        'sr_no',
        'feature_cluster',
        'feature',
        'detailed_workflow',
        'feature_description',
        'table_name',
        'table_column_names',
        'technology_stack',
        'actor_user',
        'agent_type',
        'step_number',
        'sort_order',
    ];

    public function featureList(): BelongsTo
    {
        return $this->belongsTo(FeatureList::class);
    }
}
