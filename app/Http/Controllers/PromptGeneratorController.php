<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FileReaderService;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\SavedPrompt;

class PromptGeneratorController extends Controller
{
    protected string $masterPrompt = <<<EOT
You are an Expert Assessment Designer and Auto-Grader Prompt Generator.

Your task is to read the provided Project Brief, Assignment Brief, or Assessment Guide and automatically generate a complete Auto-Grader Prompt using the EXACT TEMPLATE below.

Do not output ANY JSON structure. The final output must be exactly the text-based prompt template, with the placeholders filled in based on the assignment document you read.

=== START OF PROMPT TEMPLATE ===

## SYSTEM INSTRUCTIONS
You are an automated grading assistant. Evaluate learner submissions using only visible evidence. Never hallucinate. Never assume. Use "Not specified/Not Found" for missing information.

## CONTEXT
Assignment Title: {Extract Title}
Module Name: {Extract Module}
Scenario: {Extract Scenario or Project Context}

## SUBMISSION REQUIREMENTS
Required Files:
- {List required files}
Prohibited Files:
- {List prohibited files or say None}

## TASKS & GRADING CRITERIA

{For each task, create a block like this:}
### Task [Number] - [Task Name]
Requirements:
- {Requirement 1}
- {Requirement 2}
Deliverables:
- {Deliverable 1}
Marking Criteria:
- Evidence Required: {What evidence proves this task is done}
- Marks Awarded: {Marks for this task}

## GRADING RULES
1. If evidence exists: Award marks.
2. Deduct only when evidence is missing. Do not deduct for formatting unless specified.
3. If a file exists but cannot be parsed automatically, assume it is present for validation checks.

## FINAL INSTRUCTION
Evaluate the learner's work strictly according to the criteria defined above.

=== END OF PROMPT TEMPLATE ===

Fill out the template based on the uploaded brief. Your output should just be the completed template. Do NOT include JSON tags, arrays, or markdown code blocks for JSON.
EOT;

    public function __construct(
        protected OpenAIService $openAIService,
        protected FileReaderService $fileReaderService
    ) {}

    public function index()
    {
        $apiStatus = $this->openAIService->checkConnection();
        return view('prompt-generator.index', compact('apiStatus'));
    }

    public function generate(Request $request)
    {
        set_time_limit(300); // 5 minutes to prevent timeout on large documents

        $request->validate([
            'brief_file' => 'required|file|mimes:pdf,doc,docx,txt|max:51200',
        ]);

        try {
            $uploadFolder = sprintf('prompt-generator/%s-%s', now()->format('YmdHis'), Str::random(8));
            $filename = $this->sanitizeFilename($request->file('brief_file')->getClientOriginalName());
            
            $path = Storage::putFileAs($uploadFolder, $request->file('brief_file'), $filename);
            $fullPath = Storage::path($path);

            $briefContent = $this->fileReaderService->readPromptFile(
                $fullPath,
                $filename
            );

            if (empty(trim($briefContent)) || str_contains($briefContent, 'could not be parsed automatically')) {
                return back()->with('error', 'Could not extract text from the uploaded file. Please try a different document format.');
            }

            Log::info('Generating prompt from brief', ['file' => $filename]);

            $generatedPrompt = $this->openAIService->generatePrompt($this->masterPrompt, $briefContent);

            // Check if the AI service returned an error JSON string
            $decodedResponse = json_decode($generatedPrompt, true);
            if (is_array($decodedResponse) && isset($decodedResponse['error'])) {
                throw new \Exception($decodedResponse['error']);
            }

            // Try to extract the prompt if it got wrapped in markdown blocks
            if (preg_match('/```(?:markdown|text)?\s*(.*?)\s*```/s', $generatedPrompt, $matches)) {
                $generatedPrompt = trim($matches[1]);
            } else {
                $generatedPrompt = trim($generatedPrompt);
            }

            // The prompt is no longer auto-saved to the database
            $baseName = pathinfo($filename, PATHINFO_FILENAME);

            Storage::deleteDirectory($uploadFolder);

            return back()->with('generated_prompt', $generatedPrompt)->with('success', 'Prompt generated successfully!');

        } catch (\Exception $e) {
            Log::error('Prompt generation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate prompt: ' . $e->getMessage());
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        $clean = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        return Str::substr($clean, 0, 120);
    }
}
