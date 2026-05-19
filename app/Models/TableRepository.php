<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableRepository extends Model
{
    protected $table = 'table_repository';

    protected $fillable = [
        'table_name',
        'table_type',
        'description',
        'source_story_id',
        'usage_count',
    ];

    public function columns(): HasMany
    {
        return $this->hasMany(ColumnRepository::class, 'table_repository_id');
    }

    public function sourceStory()
    {
        return $this->belongsTo(Story::class, 'source_story_id');
    }
}
