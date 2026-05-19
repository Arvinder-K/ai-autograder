<?php

namespace App\Http\Controllers;

use App\Models\FeatureList;
use App\Models\Story;
use App\Services\AIService;
use App\Services\AuditService;
use App\Services\FeatureListService;
use App\Services\TableRepositoryService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FeatureListController extends Controller
{
    public function __construct(
        private AIService $aiService,
        private FeatureListService $featureListService,
        private TableRepositoryService $tableRepositoryService,
    ) {}

    public function index(Story $story)
    {
        $featureLists = $story->featureLists()->with('items')->get();

        return view('features.index', compact('story', 'featureLists'));
    }

    public function generate(Request $request, Story $story)
    {
        $request->validate([
            'format_type' => 'required|in:business,procode,agentic',
        ]);

        $formatType = $request->input('format_type');
        $existingTables = $this->tableRepositoryService->getRepositoryContext();

        $aiResponse = $this->aiService->generateFeatureList($story, $formatType, $existingTables);
        $featureList = $this->featureListService->createFromAIResponse($story, $formatType, $aiResponse);

        AuditService::log(
            'generate',
            'feature_list',
            $featureList->id,
            "Generated {$formatType} feature list for story: {$story->title}"
        );

        return redirect()->route('stories.features.show', [$story, $featureList])
            ->with('success', 'Feature list generated.');
    }

    public function show(Story $story, FeatureList $featureList)
    {
        $featureList->load('items');

        return view('features.show', compact('story', 'featureList'));
    }

    public function export(Story $story, FeatureList $featureList)
    {
        $featureList->load('items');

        AuditService::log('export', 'feature_list', $featureList->id, 'Exported feature list to Excel');

        return Excel::download(
            new \App\Exports\FeatureListExport($featureList),
            str_replace(' ', '_', $featureList->title) . '.xlsx'
        );
    }
}
