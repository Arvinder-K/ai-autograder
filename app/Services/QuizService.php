<?php

namespace App\Services;

use App\Models\QuizQuestion;
use App\Models\QuizResponse;
use App\Models\QuizSection;
use App\Models\Story;

class QuizService
{
    public function getSections(): \Illuminate\Database\Eloquent\Collection
    {
        return QuizSection::where('is_active', true)
            ->with(['questions' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function saveResponses(Story $story, array $responses): void
    {
        foreach ($responses as $questionId => $answer) {
            $question = QuizQuestion::find($questionId);
            if (!$question) {
                continue;
            }

            $data = [
                'story_id' => $story->id,
                'quiz_question_id' => $questionId,
            ];

            if (is_array($answer)) {
                $data['answer_options'] = $answer;
                $data['answer_value'] = implode(', ', $answer);
            } else {
                $data['answer_value'] = $answer;
            }

            QuizResponse::updateOrCreate(
                ['story_id' => $story->id, 'quiz_question_id' => $questionId],
                $data
            );
        }
    }

    public function getResponsesForStory(Story $story): array
    {
        return $story->quizResponses()
            ->with('question.section')
            ->get()
            ->groupBy(fn($r) => $r->question->section->name ?? 'Other')
            ->toArray();
    }
}
