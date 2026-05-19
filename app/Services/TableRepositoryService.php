<?php

namespace App\Services;

use App\Models\ColumnRepository;
use App\Models\FeatureItem;
use App\Models\Story;
use App\Models\TableRepository;

class TableRepositoryService
{
    public function syncFromStory(Story $story): void
    {
        $featureItems = FeatureItem::whereHas('featureList', function ($q) use ($story) {
            $q->where('story_id', $story->id);
        })->whereNotNull('table_name')->get();

        foreach ($featureItems as $item) {
            $tableNames = array_map('trim', explode(',', $item->table_name));

            foreach ($tableNames as $tableName) {
                if (empty($tableName)) {
                    continue;
                }

                $tableRepo = TableRepository::firstOrCreate(
                    ['table_name' => $tableName],
                    [
                        'table_type' => $this->inferTableType($tableName),
                        'source_story_id' => $story->id,
                    ]
                );

                $tableRepo->increment('usage_count');

                // Parse and sync columns
                if ($item->table_column_names) {
                    $columns = array_map('trim', explode(',', $item->table_column_names));
                    foreach ($columns as $colName) {
                        if (empty($colName)) {
                            continue;
                        }
                        ColumnRepository::firstOrCreate(
                            ['table_repository_id' => $tableRepo->id, 'column_name' => $colName],
                            [
                                'data_type' => 'string',
                                'source_story_id' => $story->id,
                            ]
                        );
                    }
                }
            }
        }
    }

    public function getRepositoryContext(): array
    {
        return TableRepository::with('columns')
            ->orderBy('table_name')
            ->get()
            ->map(fn($table) => [
                'table_name' => $table->table_name,
                'table_type' => $table->table_type,
                'columns' => $table->columns->pluck('column_name')->toArray(),
            ])
            ->toArray();
    }

    private function inferTableType(string $tableName): string
    {
        $masterPrefixes = ['mst_', 'master_', 'ref_', 'lookup_', 'config_'];
        foreach ($masterPrefixes as $prefix) {
            if (str_starts_with(strtolower($tableName), $prefix)) {
                return 'master';
            }
        }

        $masterKeywords = ['countries', 'states', 'cities', 'currencies', 'roles', 'permissions', 'categories', 'types', 'statuses', 'domains', 'departments'];
        foreach ($masterKeywords as $keyword) {
            if (str_contains(strtolower($tableName), $keyword)) {
                return 'master';
            }
        }

        return 'transactional';
    }
}
