<?php

namespace App\Services;

use App\Models\FeatureItem;
use App\Models\FeatureList;
use App\Models\Story;

class FeatureListService
{
    public function createFromAIResponse(Story $story, string $formatType, string $aiResponse): FeatureList
    {
        $featureList = FeatureList::create([
            'story_id' => $story->id,
            'format_type' => $formatType,
            'title' => $story->title . ' - ' . ucfirst($formatType) . ' Features',
            'description' => "Generated feature list for {$story->title}",
        ]);

        $items = $this->parseAIResponse($aiResponse, $formatType);

        foreach ($items as $index => $item) {
            FeatureItem::create([
                'feature_list_id' => $featureList->id,
                'sr_no' => $index + 1,
                'feature_cluster' => $item['feature_cluster'] ?? '',
                'feature' => $item['feature'] ?? '',
                'detailed_workflow' => $item['detailed_workflow'] ?? '',
                'feature_description' => $item['feature_description'] ?? '',
                'table_name' => $item['table_name'] ?? null,
                'table_column_names' => $item['table_column_names'] ?? null,
                'technology_stack' => $item['technology_stack'] ?? null,
                'actor_user' => $item['actor_user'] ?? null,
                'agent_type' => $item['agent_type'] ?? null,
                'step_number' => $item['step_number'] ?? $index + 1,
                'sort_order' => $index,
            ]);
        }

        return $featureList;
    }

    private function parseAIResponse(string $response, string $formatType): array
    {
        // Try JSON parsing first
        $jsonMatch = [];
        if (preg_match('/\[[\s\S]*\]/', $response, $jsonMatch)) {
            $parsed = json_decode($jsonMatch[0], true);
            if (is_array($parsed)) {
                return $this->normalizeKeys($parsed, $formatType);
            }
        }

        // Fallback: parse markdown table
        return $this->parseMarkdownTable($response, $formatType);
    }

    private function normalizeKeys(array $items, string $formatType): array
    {
        return array_map(function ($item) use ($formatType) {
            $normalized = [];
            foreach ($item as $key => $value) {
                $snake = strtolower(str_replace([' ', '-'], '_', trim($key)));
                $snake = preg_replace('/^sr\.?\s*no\.?$/', 'sr_no', $snake);
                $snake = str_replace('table_columns_names', 'table_column_names', $snake);
                $snake = str_replace('table_column_name', 'table_column_names', $snake);
                $normalized[$snake] = $value;
            }
            return $normalized;
        }, $items);
    }

    private function parseMarkdownTable(string $content, string $formatType): array
    {
        $lines = explode("\n", $content);
        $items = [];
        $headers = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '---') || str_starts_with($line, '|---')) {
                continue;
            }
            if (!str_contains($line, '|')) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($line, '|')));

            if (empty($headers)) {
                $headers = $cells;
                continue;
            }

            $row = [];
            foreach ($headers as $i => $header) {
                $snake = strtolower(str_replace([' ', '-'], '_', trim($header)));
                $row[$snake] = $cells[$i] ?? '';
            }
            $items[] = $row;
        }

        return $this->normalizeKeys($items, $formatType);
    }
}
