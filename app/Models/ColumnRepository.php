<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColumnRepository extends Model
{
    protected $table = 'column_repository';

    protected $fillable = [
        'table_repository_id',
        'column_name',
        'data_type',
        'is_primary_key',
        'is_foreign_key',
        'references_table',
        'references_column',
        'is_nullable',
        'default_value',
        'description',
        'source_story_id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary_key' => 'boolean',
            'is_foreign_key' => 'boolean',
            'is_nullable' => 'boolean',
        ];
    }

    public function tableRepository(): BelongsTo
    {
        return $this->belongsTo(TableRepository::class, 'table_repository_id');
    }
}
