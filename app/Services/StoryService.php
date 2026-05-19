<?php

namespace App\Services;

use App\Models\Story;
use App\Models\StoryVersion;
use Illuminate\Support\Facades\Auth;

class StoryService
{
    public function createVersion(Story $story, ?string $changeSummary = null): StoryVersion
    {
        $versionNumber = $story->versions()->max('version_number') ?? 0;
        $versionNumber++;

        return StoryVersion::create([
            'story_id' => $story->id,
            'version_number' => $versionNumber,
            'title' => $story->title,
            'snapshot_data' => $story->toArray(),
            'change_summary' => $changeSummary ?? 'Version ' . $versionNumber,
            'changed_by' => Auth::id(),
        ]);
    }

    public function approveStory(Story $story): Story
    {
        $story->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $this->createVersion($story, 'Approved');

        // Sync tables to repository
        app(TableRepositoryService::class)->syncFromStory($story);

        AuditService::log('approve', 'story', $story->id, "Story '{$story->title}' approved");

        return $story;
    }

    public function buildStoryFromQuizResponses(Story $story): array
    {
        $responses = $story->quizResponses()->with('question.section')->get();

        $quizData = [];
        foreach ($responses as $response) {
            $sectionSlug = $response->question->section->slug ?? 'unknown';
            $quizData[$sectionSlug][] = [
                'question' => $response->question->question_text,
                'answer' => $response->answer_options ?? $response->answer_value,
            ];
        }

        return $quizData;
    }
}
