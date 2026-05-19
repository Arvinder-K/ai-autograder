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
            'student_name' => 'required|string|max:255',
            'assignment_name' => 'required|string|max:255',
            'prompt_file' => 'required|file|mimes:txt,pdf,docx,doc|max:51200',
            'zip_file' => 'required|file|mimes:zip|max:51200',
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
