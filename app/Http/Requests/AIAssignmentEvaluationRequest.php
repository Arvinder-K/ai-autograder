<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AIAssignmentEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'saved_prompt_id' => 'required|exists:saved_prompts,id',
            'zip_file' => 'required|file|mimes:zip|max:102400',
        ];
    }

    public function messages(): array
    {
        return [
            'prompt_file.mimes' => 'Prompt file must be a txt, pdf, doc, or docx file.',
            'prompt_file.max' => 'Prompt file must not exceed 50MB.',
            'zip_file.mimes' => 'ZIP upload must be a valid zip archive.',
            'zip_file.max' => 'ZIP file must not exceed 50MB.',
        ];
    }
}
