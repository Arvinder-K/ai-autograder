<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\AIService;
use App\Services\AuditService;
use App\Services\QuizService;
use App\Services\StoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function __construct(
        private StoryService $storyService,
        private QuizService $quizService,
        private AIService $aiService,
    ) {}

    public function index(Request $request)
    {
        $query = Story::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Show own stories first, then others
        $stories = $query->paginate(15);

        return view('stories.index', compact('stories'));
    }

    public function create()
    {
        return view('stories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'creation_mode' => 'required|in:quiz,description',
            'process_type' => 'required|in:single,multi',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';

        $story = Story::create($validated);

        AuditService::log('create', 'story', $story->id, "Created story: {$story->title}");

        if ($validated['creation_mode'] === 'quiz') {
            return redirect()->route('stories.quiz', $story);
        }

        return redirect()->route('stories.edit', $story);
    }

    public function show(Story $story)
    {
        $story->load(['user', 'versions', 'featureLists.items', 'technicalDesigns', 'quizResponses.question.section']);

        $isOwner = $story->isOwner(Auth::user());

        return view('stories.show', compact('story', 'isOwner'));
    }

    public function edit(Story $story)
    {
        if (!$story->canBeEdited(Auth::user())) {
            return redirect()->route('stories.show', $story)
                ->with('error', 'This story cannot be edited.');
        }

        return view('stories.edit', compact('story'));
    }

    public function update(Request $request, Story $story)
    {
        if (!$story->canBeEdited(Auth::user())) {
            return back()->with('error', 'This story cannot be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'generated_story' => 'nullable|string',
        ]);

        // Create version before update
        $this->storyService->createVersion($story, 'Manual edit');

        $oldValues = $story->only(['title', 'description']);
        $story->update($validated);
        $story->increment('version');

        AuditService::log('update', 'story', $story->id, "Updated story: {$story->title}", $oldValues, $validated);

        return redirect()->route('stories.show', $story)->with('success', 'Story updated successfully.');
    }

    public function destroy(Story $story)
    {
        if (!$story->isOwner(Auth::user())) {
            abort(403);
        }

        if (!$story->canBeDeleted()) {
            return back()->with('error', 'Approved stories cannot be deleted.');
        }

        AuditService::log('delete', 'story', $story->id, "Deleted story: {$story->title}");
        $story->delete();

        return redirect()->route('stories.index')->with('success', 'Story deleted.');
    }

    public function quiz(Story $story)
    {
        $sections = $this->quizService->getSections();
        $existingResponses = $story->quizResponses()->pluck('answer_value', 'quiz_question_id')->toArray();

        return view('stories.quiz', compact('story', 'sections', 'existingResponses'));
    }

    public function saveQuiz(Request $request, Story $story)
    {
        $responses = $request->input('responses', []);
        $this->quizService->saveResponses($story, $responses);

        AuditService::log('update', 'story', $story->id, 'Quiz responses saved');

        return redirect()->route('stories.quiz', $story)->with('success', 'Responses saved.');
    }

    public function generateStory(Story $story)
    {
        $quizData = $this->storyService->buildStoryFromQuizResponses($story);
        $generatedStory = $this->aiService->generateUserStory($story, $quizData);

        $this->storyService->createVersion($story, 'AI story generation');

        $story->update([
            'generated_story' => $generatedStory,
            'ai_analysis' => json_encode($quizData),
        ]);
        $story->increment('version');

        AuditService::log('generate', 'story', $story->id, 'AI-generated user story');

        return redirect()->route('stories.show', $story)->with('success', 'User story generated successfully.');
    }

    public function uploadDocument(Request $request, Story $story)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,txt,md|max:10240',
        ]);

        $path = $request->file('document')->store('story-documents', 'local');
        $content = '';

        // Extract text content for AI analysis
        $extension = $request->file('document')->getClientOriginalExtension();
        if (in_array($extension, ['txt', 'md'])) {
            $content = Storage::disk('local')->get($path);
        }

        $story->update(['uploaded_document_path' => $path]);

        if ($content) {
            $analysis = $this->aiService->analyzeDocument($content);
            $story->update(['ai_analysis' => $analysis]);
        }

        AuditService::log('upload', 'story', $story->id, 'Document uploaded');

        return back()->with('success', 'Document uploaded and analyzed.');
    }

    public function approve(Story $story)
    {
        $this->storyService->approveStory($story);

        return redirect()->route('stories.show', $story)->with('success', 'Story approved.');
    }

    public function versions(Story $story)
    {
        $versions = $story->versions()->with('changedByUser')->paginate(20);

        return view('stories.versions', compact('story', 'versions'));
    }

    public function deleteVersion(Story $story, int $versionId)
    {
        $version = $story->versions()->findOrFail($versionId);
        $version->delete();

        return back()->with('success', 'Version deleted.');
    }
}
