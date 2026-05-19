<?php

namespace App\Http\Controllers;

use App\Models\CodingPrompt;
use App\Models\Configuration;
use App\Models\Story;
use App\Services\AIService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodingPromptController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function index(Story $story)
    {
        $prompts = $story->codingPrompts()->orderBy('prompt_sequence')->get();

        return view('prompts.index', compact('story', 'prompts'));
    }

    public function generate(Request $request, Story $story)
    {
        $request->validate([
            'target_tool' => 'required|in:claude_ai,github_copilot,other',
        ]);

        $targetTool = $request->input('target_tool');
        $maxTokens = (int) Configuration::getValue('ai', 'micro_prompt_max_tokens', 2000);

        $featureData = $story->featureLists()
            ->with('items')
            ->get()
            ->flatMap(fn($fl) => $fl->items)
            ->toArray();

        $microPrompts = $this->aiService->generateCodingPrompts($featureData, $targetTool, $maxTokens);

        $parentId = null;
        foreach ($microPrompts as $mp) {
            $prompt = CodingPrompt::create([
                'story_id' => $story->id,
                'title' => $story->title . ' - Prompt #' . $mp['sequence'],
                'prompt_sequence' => $mp['sequence'],
                'target_tool' => $targetTool,
                'prompt_content' => $mp['content'],
                'is_continuation' => $mp['is_continuation'],
                'parent_prompt_id' => $parentId,
                'created_by' => Auth::id(),
            ]);
            $parentId = $prompt->id;
        }

        AuditService::log('generate', 'coding_prompt', $story->id, "Generated " . count($microPrompts) . " micro-prompts for {$targetTool}");

        return redirect()->route('stories.prompts.index', $story)
            ->with('success', count($microPrompts) . ' coding prompts generated.');
    }

    public function show(Story $story, CodingPrompt $prompt)
    {
        return view('prompts.show', compact('story', 'prompt'));
    }
}
