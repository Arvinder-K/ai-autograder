<?php

namespace App\Http\Controllers;

use App\Models\SavedPrompt;
use App\Services\FileReaderService;
use Illuminate\Http\Request;

class SavedPromptController extends Controller
{
    protected FileReaderService $fileReaderService;

    public function __construct(FileReaderService $fileReaderService)
    {
        $this->fileReaderService = $fileReaderService;
    }

    public function index()
    {
        return response()->json(SavedPrompt::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'prompt_file' => 'required|file|mimes:txt,pdf,docx,doc|max:51200',
        ]);

        $file = $request->file('prompt_file');
        
        $content = $this->fileReaderService->readPromptFile(
            $file->getRealPath(),
            $file->getClientOriginalName()
        );

        $prompt = SavedPrompt::create([
            'title' => $request->title,
            'content' => $content,
            'original_filename' => $file->getClientOriginalName()
        ]);

        return response()->json(['message' => 'Prompt saved successfully', 'prompt' => $prompt]);
    }

    public function update(Request $request, SavedPrompt $prompt)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $prompt->title = $request->title;
        
        if ($request->hasFile('prompt_file')) {
            $request->validate([
                'prompt_file' => 'file|mimes:txt,pdf,docx,doc|max:51200',
            ]);
            
            $file = $request->file('prompt_file');
            $content = $this->fileReaderService->readPromptFile(
                $file->getRealPath(),
                $file->getClientOriginalName()
            );
            
            $prompt->content = $content;
            $prompt->original_filename = $file->getClientOriginalName();
        }

        $prompt->save();

        return response()->json(['message' => 'Prompt updated successfully', 'prompt' => $prompt]);
    }

    public function destroy(SavedPrompt $prompt)
    {
        $prompt->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Prompt deleted successfully']);
        }
        
        return back()->with('success', 'Prompt deleted successfully!');
    }
}
