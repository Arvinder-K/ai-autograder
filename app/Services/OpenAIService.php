<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $provider;
    private ?string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'openai');

        if ($this->provider === 'gemini') {
            $this->apiKey = config('services.ai.gemini_api_key');
            $this->model = config('services.ai.gemini_model', 'gemini-1.5-flash');
        } else {
            $this->apiKey = config('services.ai.api_key') ?: env('OPENAI_API_KEY');
            $this->model = config('services.ai.model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
        }

        $this->maxTokens = (int) (config('services.ai.max_tokens') ?: 8192);
        $this->temperature = (float) (config('services.ai.temperature') ?: 0.7);

        Log::info('AI Service initialized', [
            'provider' => $this->provider,
            'model' => $this->model,
            'has_api_key' => !empty($this->apiKey),
        ]);
    }

    public function checkConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'connected' => false,
                'message' => ucfirst($this->provider) . ' API key not configured',
            ];
        }

        if ($this->provider === 'gemini') {
            return $this->checkGeminiConnection();
        }

        return $this->checkOpenAIConnection();
    }

    private function checkOpenAIConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->when(app()->environment('local'), fn($q) => $q->withoutVerifying())
                ->timeout(15)
                ->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                return [
                    'connected' => true,
                    'message' => 'OpenAI connected successfully',
                ];
            }

            return [
                'connected' => false,
                'message' => 'Unable to reach OpenAI service: ' . $response->status(),
            ];
        } catch (\Exception $exception) {
            return [
                'connected' => false,
                'message' => 'OpenAI connection failed: ' . $exception->getMessage(),
            ];
        }
    }

    private function checkGeminiConnection(): array
    {
        try {
            $response = Http::when(app()->environment('local'), fn($q) => $q->withoutVerifying())
                ->timeout(15)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}");

            if ($response->successful()) {
                return [
                    'connected' => true,
                    'message' => 'Gemini connected successfully',
                ];
            }

            return [
                'connected' => false,
                'message' => 'Unable to reach Gemini service: ' . $response->status(),
            ];
        } catch (\Exception $exception) {
            return [
                'connected' => false,
                'message' => 'Gemini connection failed: ' . $exception->getMessage(),
            ];
        }
    }

    public function generateEvaluation(string $prompt, array $attachments = []): string
    {
        $systemPrompt = <<<'SYSTEM'
You are an AI Auto Grading System for programming assignments.

The system receives TWO INPUTS:

1. Assignment Prompt File (The Instructor's uploaded Assignment Guidelines / Instructions document)
   - Can be PDF, DOCX, TXT, or Markdown
   - Contains assignment instructions, rubric, tasks, objectives, expected outputs, and grading criteria

2. Student Submission ZIP
   - Contains source code files
   - May contain reports, screenshots, PDFs, DOCX, images, README files, and project folders

====================================================
PRIMARY OBJECTIVE
====================================================

Your task is to evaluate student submissions strictly according to the uploaded Assignment Prompt File (the Instructor's Assignment Guidelines).

The Assignment Prompt File is the SOLE SOURCE OF TRUTH. Every criteria name, task, score, and requirement MUST come directly from it.

====================================================
⚠️  CRITICAL ENFORCEMENT — READ BEFORE ALL STEPS
====================================================

1. PROMPT FILE IS LAW: Derive 100% of grading criteria, task names, marks, and requirements FROM the assignment prompt. Never invent or assume criteria not present in the prompt. If a file cannot be read or verified:
   - Do not infer its contents.
   - Do not assume functionality exists.
   - Mark the requirement as "Not Verifiable".
   - Do not award marks based on assumptions.
2. USE EXACT NAMES: When the prompt says "Task 1: Test Plan (30 marks)" — use exactly that text as the criteria name. Do not paraphrase it.
3. USE EXACT MARKS: When the prompt says a task is worth 30 marks — score it out of 30. Never change the mark allocation.
4. DO NOT USE GENERIC CRITERIA: Never use generic categories like "Code Quality", "Documentation", or "Submission Requirements" unless the assignment prompt explicitly lists them with marks.
5. NEVER ADD EXTRA CRITERIA: If the prompt has 3 tasks, output exactly 3 grading_criteria entries. Do not add extra categories.
6. EVERYTHING MUST BE DYNAMIC: Do not hardcode assignment names, file names, technologies, rubrics, languages, or expected functions.
7. EVIDENCE-BASED FEEDBACK: Your feedback must sound professional and academic. Use phrases like "The learner demonstrates...", "Evidence of X was found in file Y...", "The implementation of Z follows the requirements...". Quote actual class and method names found in the code.
8. DEEP LOGICAL VERIFICATION: You must perform a deep, comprehensive, line-by-line verification of the student's submitted code against the complete logic, rules, business requirements, and constraints specified in the Assignment Prompt File. Do not perform surface-level checks. You must trace variable initializations (e.g. starting seats at 20, price at 1.50), arithmetic operators and mathematical formulas (e.g. price * quantity, decrementing of available seats), loop conditions (continuation prompts, exit conditions), boundary validation, and exception handling. If any logical step or rule is missing, incorrect, or partially implemented, you must penalize the score strictly and identify the exact discrepancy and missing logic in your feedback and suggestions.
9. BE EXTREMELY STRICT AND CRITICAL: Do NOT simply give 100% scores. Start every criteria at 0 marks and ONLY award points for undeniable, concrete evidence of correct implementation. If the logic is flawed, files are missing, or a requirement is only partially met, you MUST deduct points. You are instructed to be an extremely rigorous, unforgiving grader.

====================================================
STEP 1 — ANALYZE ASSIGNMENT PROMPT (THIS IS YOUR GRADING BIBLE)
====================================================

Read the entire prompt document carefully from start to finish.

Extract and record EXACTLY:

1. Assignment Title (use verbatim)
2. Assignment Objective
3. Every Task/Section listed (use EXACT names and numbers from the prompt)
4. Marks allocated per Task/Section (use EXACT values from the prompt)
5. Required Files (filenames, formats)
6. Required Technologies / Languages / Frameworks
7. Required Functions, Classes, or Methods
8. Required Outputs / Results
9. Required Reports / Documents
10. Required Screenshots or Visual Evidence
11. The complete Marking Rubric (task by task)
12. Specific Business Rules or Validation Rules
13. Coding Standards Requirements
14. Submission Requirements

This extracted model is your ONLY reference for grading. Do not supplement it with assumptions or prior AI knowledge.

====================================================
STEP 2 — ANALYZE STUDENT ZIP
====================================================

Extract and inspect all files from ZIP.

Analyze:
- Source code
- Folder structure
- Reports
- PDFs
- DOCX files
- Images/screenshots
- README files
- Database files
- Config files

Detect:
- Programming language
- Framework
- Libraries
- APIs
- Unit tests
- Database integration
- UI components
- Business logic

====================================================
STEP 3 — MATCH SUBMISSION AGAINST ASSIGNMENT
====================================================

Compare the extracted assignment requirements against the student submission.

Check dynamically for:

A. Required Features
- Are all required features implemented?

B. Required Files
- Are required files present?

C. Business Logic
- Does the code satisfy the core assignment rules?
- Deeply verify specific requested workflows (e.g., Login, Authentication, Validation, Calculations) to ensure the logic actually exists and matches the assignment requirements.

D. Code Quality
- Readability
- Structure
- Naming
- Comments
- Documentation

E. Functional Coverage
- Functions/classes exist
- Required logic exist
- API routes exist
- Database operations exist

F. Testing
- Unit tests
- Assertions
- Test methods
- Output screenshots

G. Documentation
- Reports
- Explanations
- Screenshots
- README

====================================================
STEP 4 — VALIDATION RULES
====================================================

INVALID SUBMISSION if:
- ZIP is corrupted
- No readable files
- Empty submission
- Only copied assignment prompt
- No actual learner work
- Missing core project files

If invalid:
- All scores = 0
- Return INVALID submission status

====================================================
STEP 5 — SCORING LOGIC (STRICTLY FROM PROMPT FILE)
====================================================

Generate grading_criteria entries ONLY from the assignment prompt.

Rule A — USE EXACT CRITERIA FROM PROMPT:
- If the prompt says "Task 1: Test Plan — 30 marks", create one entry: criteria="Task 1: Test Plan", score out of 30.
- If the prompt says "Algorithm Accuracy — 25%", create one entry: criteria="Algorithm Accuracy", score out of 25.
- Copy task names verbatim. Do not rename, merge, or split them.

Rule B — TOTAL MARKS:
- The sum of all criteria scores must equal the total marks stated in the prompt.
- Never inflate or reduce the total.

Rule D — SCORE FORMAT:
- EVERY individual score in the 'grading_criteria' list MUST be formatted as "Earned / Max" (e.g., "25 / 30").
- NEVER output just a number (e.g., do not output "25").
- The 'Max' value must match the marks allocated in the assignment prompt.

Scoring Philosophy:
- STRICT EVALUATION: Grade strictly on correctness, completeness, and adherence to requirements. Do NOT give full marks just for effort.
- ZERO BY DEFAULT: Assume a task score is 0. Only award marks if the exact requested logic, files, and requirements are perfectly present.
- DEDUCT POINTS FREELY: If there are bugs, missing logic, missing validation, or missing files, deduct points heavily. A 100% score should be extremely rare and only for flawless submissions.
- Partial credit is allowed ONLY when a student partially completes a requirement, but do not give more than 50% for partial work that doesn't fully run or logic that is flawed.
- SIMPLE FEEDBACK: Keep the feedback extremely brief, clear, and direct. Do NOT write long paragraphs. Do NOT explicitly cite file names (e.g., avoid saying 'in the file template.pdf'), exact task names, or page numbers in your feedback.
- TONE AND STYLE: Write feedback exactly in the style of this example: "The learner created a structured tourism homepage using semantic HTML elements... The webpage includes all required sections such as navigation menu... However, there are naming inconsistencies because the stylesheet path uses Css/style.css while the folder shown is css... Suggestion: Use consistent lowercase folder names such as css and images to fully match standard web development conventions."
- ACTION PLAN: Write the action plan as clear, concise bullet points (e.g. "Good work—your tourism homepage structure and CSS styling demonstrate good beginner-level web development understanding. Improve folder naming consistency...")
- SPECIFIC SUGGESTIONS: When providing suggestions for fixing/improvement, provide actual code snippets or concrete steps, not just vague advice. If you deduct ANY points from the max score, you MUST provide a specific 'fixing' suggestion. Do not output 'No fixing required' if the score is not perfect!
- CROSS-REFERENCE: If a requirement in the prompt is missing in the code, check if it was addressed in the student's report (PDF/DOCX). Mention this in your feedback.

====================================================
STEP 6 — CODE ANALYSIS LOGIC
====================================================

Analyze code semantically.

Check:
- Functions/classes exist
- Logic correctness
- API endpoints
- Validation logic
- Database queries
- MVC structure
- Security basics
- Error handling
- Comments/documentation

Laravel-specific checks:
- Routes
- Controllers
- Models
- Migrations
- Blade templates
- Middleware
- API controllers
- Validation rules
- Environment structure

Python-specific checks:
- Functions
- Classes
- unittest/pytest
- Logic validation

Frontend checks:
- HTML structure
- CSS styling
- Responsive layout
- JavaScript functionality

====================================================
STEP 7 — OUTPUT FORMAT
====================================================

Return ONLY valid JSON.

Structure:

{
  "assignment_title": "",
  "technology_detected": [],
  "grading_criteria": [
    {
      "criteria": "Task Name Verbatim",
      "feedback": "Brief, concise feedback (Keep under 3 sentences)",
      "fixing": "Brief, actionable steps to improve. (MANDATORY if points are deducted - do not leave empty!)",
      "score": "Earned / Max (e.g., 25 / 30)"
    }
  ],
  "detected_files": [],
  "missing_requirements": [],
  "summary": {
    "max_score": "",
    "earned_score": "",
    "percentage": "",
    "grade": "",
    "ksa_index": ""
  },
  "strengths": [],
  "weaknesses": [],
  "action_plan": ""
}

====================================================
IMPORTANT GUARDRAILS
====================================================

- Never hallucinate missing files
- Never assume functionality that does not exist
- Use only visible evidence
- If uncertain, mark as: "Not Found"
- Be deterministic and consistent
- Do not generate fake code analysis
- Do not add markdown
- Do not add explanations outside JSON
- SCREENSHOTS/IMAGES/FLOWCHARTS: The text extractor strips visual elements from reports. However, the student's actual PDF and image files are ATTACHED to this request as binary attachments (inlineData for Gemini / image_url for OpenAI). 
  1. MULTIMODAL INSPECTION: You MUST look at the attached PDF(s) and image file(s) to visually check for the existence, correctness, and design of any required flowcharts, screenshots, reports, and diagrams. 
  2. QUALITY/CORRECTNESS: Do not just assume a flowchart is correct because a file exists. Inspect the visual flowchart's actual logic, symbols, decisions, calculations, and inputs. Ensure the layout and design align with the assignment prompt.
  3. FEEDBACK: In your feedback, explicitly refer to what you observed inside the attached PDF/image file (e.g., "The flowchart in the attached PDF correctly shows the available_seats initialization and the ticket purchase logic loop").
- EMBEDDED CODE IN REPORTS: Students sometimes paste their source code or test plans directly into their PDF/DOCX report instead of submitting separate code files. If you cannot find a required code file, you MUST scan the text of all submitted PDF/DOCX reports. If the required code or logic is found embedded within the report's text, accept it as valid and grade it normally. Do not penalize for missing files if the content exists within the report.

====================================================
SPECIAL REQUIREMENTS
====================================================

The system must support:
- Multiple assignment types
- Dynamic rubric generation
- Mixed file formats
- ZIP source code inspection
- DOCX/PDF report analysis
- Screenshot interpretation
- Cross-language grading

The system should behave like a professional university auto-grading engine.
SYSTEM;

        if ($this->provider === 'gemini') {
            return $this->callGemini($systemPrompt, $prompt, $attachments);
        }

        return $this->callOpenAI($systemPrompt, $prompt, $attachments);
    }

    public function generatePrompt(string $masterPrompt, string $assignmentBriefContent): string
    {
        if ($this->provider === 'gemini') {
            return $this->callGemini($masterPrompt, $assignmentBriefContent, []);
        }

        return $this->callOpenAI($masterPrompt, $assignmentBriefContent, []);
    }

    private function callOpenAI(string $systemPrompt, string $userPrompt, array $attachments = []): string
    {
        try {
            $cleanPrompt = iconv('UTF-8', 'UTF-8//IGNORE', $userPrompt);

            $content = [
                ['type' => 'text', 'text' => $cleanPrompt]
            ];

            foreach ($attachments as $filePath) {
                if (file_exists($filePath)) {
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                        $mimeType = match ($extension) {
                            'png' => 'image/png',
                            'jpg', 'jpeg' => 'image/jpeg',
                            'gif' => 'image/gif',
                            'webp' => 'image/webp',
                        };
                        $fileName = basename($filePath);
                        $content[] = [
                            'type' => 'text',
                            'text' => "=== ATTACHMENT: {$fileName} ==="
                        ];
                        $content[] = [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64," . base64_encode(file_get_contents($filePath))
                            ]
                        ];
                    }
                }
            }

            $url = 'https://api.openai.com/v1/chat/completions';
            $data = [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $content],
                ],
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
            ];

            $maxAttempts = 5;
            $response = null;
            $statusCode = 0;
            $curlError = null;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer {$this->apiKey}",
                    "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);

                $response = curl_exec($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    Log::error("OpenAI cURL Request Attempt {$attempt} Error", ['error' => $curlError]);
                    if ($attempt < $maxAttempts) {
                        sleep(5);
                        continue;
                    }
                    break;
                }

                if ($statusCode === 429) {
                    Log::warning("OpenAI API rate limit hit (429) on attempt {$attempt}. Sleeping 10 seconds before retry...");
                    if ($attempt < $maxAttempts) {
                        sleep(10);
                        continue;
                    }
                }

                break;
            }

            if ($curlError) {
                return json_encode([
                    'error' => "cURL error: {$curlError}",
                    'overall_score' => 0,
                    'learner_feedback' => "cURL connection failed: {$curlError}"
                ]);
            }

            if ($statusCode >= 200 && $statusCode < 300) {
                $responseData = json_decode($response, true);
                return $responseData['choices'][0]['message']['content'] ?? '';
            }

            Log::error('OpenAI API request failed via cURL', [
                'status' => $statusCode,
                'response' => mb_substr($response, 0, 500)
            ]);

            return json_encode([
                'error' => "AI API error: {$statusCode}",
                'overall_score' => 0,
                'learner_feedback' => "An error occurred with the AI service ({$statusCode}). Please try again."
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function callGemini(string $systemPrompt, string $userPrompt, array $attachments = []): string
    {
        try {
            $cleanPrompt = iconv('UTF-8', 'UTF-8//IGNORE', $userPrompt);

            // For Gemini, we combine system and user prompts or use specific instructions
            $fullPrompt = "SYSTEM INSTRUCTIONS:\n{$systemPrompt}\n\nUSER SUBMISSION TO EVALUATE:\n{$cleanPrompt}";

            $parts = [
                ['text' => $fullPrompt]
            ];

            foreach ($attachments as $filePath) {
                if (file_exists($filePath)) {
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $mimeType = match ($extension) {
                        'pdf' => 'application/pdf',
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        default => null
                    };

                    if ($mimeType) {
                        $fileName = basename($filePath);
                        $parts[] = [
                            'text' => "=== ATTACHMENT: {$fileName} ==="
                        ];
                        $parts[] = [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => base64_encode(file_get_contents($filePath))
                            ]
                        ];
                    }
                }
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
            $data = [
                'contents' => [
                    [
                        'parts' => $parts
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature,
                    'maxOutputTokens' => $this->maxTokens
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_NONE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_NONE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_NONE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_NONE'
                    ]
                ]
            ];

            $maxAttempts = 5;
            $response = null;
            $statusCode = 0;
            $curlError = null;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);

                $response = curl_exec($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    Log::error("Gemini cURL Request Attempt {$attempt} Error", ['error' => $curlError]);
                    if ($attempt < $maxAttempts) {
                        sleep(5);
                        continue;
                    }
                    break;
                }

                if ($statusCode === 429) {
                    Log::warning("Gemini API rate limit hit (429) on attempt {$attempt}. Sleeping 25 seconds before retry...");
                    if ($attempt < $maxAttempts) {
                        sleep(25);
                        continue;
                    }
                }

                break;
            }

            if ($curlError) {
                return json_encode([
                    'error' => "cURL error: {$curlError}",
                    'overall_score' => 0,
                    'learner_feedback' => "cURL connection failed: {$curlError}"
                ]);
            }

            if ($statusCode >= 200 && $statusCode < 300) {
                $responseData = json_decode($response, true);
                
                $finishReason = $responseData['candidates'][0]['finishReason'] ?? 'UNKNOWN';
                Log::info('Gemini API response success', [
                    'finishReason' => $finishReason,
                    'usageMetadata' => $responseData['usageMetadata'] ?? []
                ]);

                $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

                // Gemini sometimes wraps JSON in markdown blocks even with responseMimeType
                // Use a robust regex that handles text before/after the code block
                if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $text, $codeBlockMatch)) {
                    $text = $codeBlockMatch[1];
                } elseif (str_contains($text, '```')) {
                    // Fallback: strip all ``` markers
                    $text = preg_replace('/```(?:json)?\s*/s', '', $text);
                }

                return trim($text);
            }

            Log::error('Gemini API request failed via cURL', [
                'status' => $statusCode,
                'response' => mb_substr($response, 0, 500)
            ]);

            return json_encode([
                'error' => "AI API error: {$statusCode}",
                'overall_score' => 0,
                'learner_feedback' => "An error occurred with the AI service ({$statusCode}). Please try again."
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleErrorResponse($response): string
    {
        $statusCode = $response->status();
        $body = $response->body();

        Log::error('AI API request failed', [
            'provider' => $this->provider,
            'status' => $statusCode,
            'body' => mb_substr($body, 0, 500)
        ]);

        if ($statusCode === 429) {
            return json_encode([
                'error' => 'Rate limit exceeded or insufficient quota.',
                'overall_score' => 0,
                'learner_feedback' => 'The evaluation service is temporarily overloaded or out of credits. Please try again later.',
            ]);
        }

        return json_encode([
            'error' => "AI API error: {$statusCode}",
            'overall_score' => 0,
            'learner_feedback' => "An error occurred with the AI service ({$statusCode}). Please check your configuration and try again.",
        ]);
    }

    private function handleException(\Exception $e): string
    {
        Log::error('AI Service Exception', [
            'provider' => $this->provider,
            'message' => $e->getMessage()
        ]);

        return json_encode([
            'error' => $e->getMessage(),
            'overall_score' => 0,
            'learner_feedback' => 'An unexpected error occurred while processing your evaluation. Please try again later.',
        ]);
    }
}
