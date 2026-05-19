<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\TechnicalDesign;
use App\Services\AIService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicalDesignController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function index(Story $story)
    {
        $designs = $story->technicalDesigns()->with('creator')->latest()->get();

        return view('designs.index', compact('story', 'designs'));
    }

    public function generate(Request $request, Story $story)
    {
        $request->validate([
            'audience' => 'required|in:technical,business,both',
        ]);

        $featureData = $story->featureLists()
            ->with('items')
            ->get()
            ->flatMap(fn($fl) => $fl->items)
            ->toArray();

        $aiResponse = $this->aiService->generateTechnicalDesign($story, $featureData);

        $design = TechnicalDesign::create([
            'story_id' => $story->id,
            'title' => $story->title . ' - Technical Design',
            'system_architecture' => $aiResponse,
            'design_details' => $aiResponse,
            'audience' => $request->input('audience'),
            'created_by' => Auth::id(),
        ]);

        AuditService::log('generate', 'technical_design', $design->id, 'Generated technical design');

        return redirect()->route('stories.designs.show', [$story, $design])
            ->with('success', 'Technical design generated.');
    }

    public function show(Story $story, TechnicalDesign $design)
    {
        return view('designs.show', compact('story', 'design'));
    }

    public function update(Request $request, Story $story, TechnicalDesign $design)
    {
        $validated = $request->validate([
            'system_architecture' => 'nullable|string',
            'design_details' => 'nullable|string',
            'presentation_content' => 'nullable|string',
        ]);

        $design->update($validated);

        AuditService::log('update', 'technical_design', $design->id, 'Updated technical design');

        return back()->with('success', 'Technical design updated.');
    }
}
