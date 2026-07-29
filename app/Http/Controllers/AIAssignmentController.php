<?php

namespace App\Http\Controllers;

use App\Http\Requests\AIAssignmentEvaluationRequest;
use App\Models\AIAssignmentEvaluation;
use App\Services\FileReaderService;
use App\Services\OpenAIService;
use App\Services\ZipExtractorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AIAssignmentController extends Controller
{
    public function __construct(
        protected OpenAIService $openAIService,
        protected FileReaderService $fileReaderService,
        protected ZipExtractorService $zipExtractorService
    ) {
    }

    public function index()
    {
        $evaluations = AIAssignmentEvaluation::latest()->take(5)->get();
        $apiStatus = $this->openAIService->checkConnection();

        $latestEvaluation = null;
        $isJson = false;
        $evaluationData = null;
        if (session()->has('latest_evaluation_id')) {
            $latestEvaluation = AIAssignmentEvaluation::find(session('latest_evaluation_id'));
            if ($latestEvaluation) {
                $repairedJson = $this->repairTruncatedJson($latestEvaluation->evaluation_report);
                $evaluationData = json_decode($repairedJson, true);
                $isJson = json_last_error() === JSON_ERROR_NONE;
                if (!$isJson) {
                    $latestEvaluation->evaluation_report = "JSON Error: " . json_last_error_msg() . "\n\n" . $latestEvaluation->evaluation_report;
                } else {
                    $latestEvaluation->evaluation_report = $repairedJson; // pass repaired string to view
                }
            }
        }

        return view('ai-evaluator.index', compact('evaluations', 'apiStatus', 'latestEvaluation', 'isJson', 'evaluationData'));
    }

    public function process(AIAssignmentEvaluationRequest $request)
    {
        set_time_limit(300);
        try {
            // Clear previous uploads but KEEP evaluations in database
            Storage::deleteDirectory('uploads/ai-evaluator');
            
            $validated = $request->validated();

            $uploadFolder = sprintf('ai-evaluator/%s-%s', now()->format('YmdHis'), Str::random(8));
            $storageFolder = "uploads/{$uploadFolder}";

            Log::info('AI assignment upload received', [
                'upload_folder' => $storageFolder,
            ]);

            $savedPrompt = \App\Models\SavedPrompt::findOrFail($request->saved_prompt_id);
            $promptContent = $savedPrompt->content;

            $zipFilename = $this->sanitizeFilename($request->file('zip_file')->getClientOriginalName());
            $zipPath = Storage::putFileAs($storageFolder, $request->file('zip_file'), $zipFilename);

            $extractionPath = Storage::path("{$storageFolder}/extracted");
            $extractedFiles = $this->zipExtractorService->extract($request->file('zip_file'), $extractionPath, 500);

            // Extract media files (flowcharts/images) from any DOCX files inside the extraction path
            if (is_dir($extractionPath)) {
                try {
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractionPath));
                    foreach ($iterator as $file) {
                        if ($file->isFile() && strtolower($file->getExtension()) === 'docx') {
                            $zip = new \ZipArchive();
                            if ($zip->open($file->getRealPath()) === true) {
                                for ($i = 0; $i < $zip->numFiles; $i++) {
                                    $name = $zip->getNameIndex($i);
                                    if (str_starts_with($name, 'word/media/')) {
                                        $imageData = $zip->getFromIndex($i);
                                        $imageExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                        if (in_array($imageExt, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                                            $imageFilename = 'docx_media_' . Str::random(8) . '_' . basename($name);
                                            // Put it in the same folder where the DOCX file is located
                                            $targetPath = dirname($file->getRealPath()) . DIRECTORY_SEPARATOR . $imageFilename;
                                            file_put_contents($targetPath, $imageData);
                                        }
                                    }
                                }
                                $zip->close();
                            }
                        }
                    }
                } catch (\Exception $ex) {
                    Log::warning('Failed to extract media from DOCX files', [
                        'error' => $ex->getMessage()
                    ]);
                }
            }

            if (isset($savedPrompt)) {
                $promptContent = $savedPrompt->content;
            } else {
                $promptContent = $this->fileReaderService->readPromptFile(
                    $request->file('prompt_file')->getRealPath(),
                    $request->file('prompt_file')->getClientOriginalName()
                );
            }
            $submissionContent = $this->fileReaderService->readExtractedFiles($extractionPath);

            $aiPrompt = $this->buildAIPrompt($promptContent, $submissionContent, $zipFilename);

            Log::info('AI prompt assembled', [
                'extracted_files' => count($extractedFiles),
                'submission_snippet' => mb_substr($submissionContent, 0, 500) . '...',
            ]);

            // Scan for PDF and image files as attachments for multimodal evaluation
            $attachments = [];
            $totalSize = 0;
            if (is_dir($extractionPath)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractionPath));
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $extension = strtolower($file->getExtension());
                        if (in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                            $size = $file->getSize();
                            // Skip files larger than 10MB
                            if ($size > 10 * 1024 * 1024) {
                                continue;
                            }
                            // Stop if total size would exceed 15MB to avoid payload size limit issues
                            if ($totalSize + $size > 15 * 1024 * 1024) {
                                break;
                            }
                            $attachments[] = $file->getRealPath();
                            $totalSize += $size;
                        }
                    }
                }
            }

            $evaluationReport = $this->openAIService->generateEvaluation($aiPrompt, $attachments);

            Log::debug('AI raw response', ['body' => $evaluationReport]);
            
            // Robust JSON extraction
            $jsonContent = '';
            if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $evaluationReport, $matches)) {
                $jsonContent = $matches[1];
            } else {
                $firstBrace = strpos($evaluationReport, '{');
                $lastBrace = strrpos($evaluationReport, '}');
                
                if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                    $jsonContent = substr($evaluationReport, $firstBrace, $lastBrace - $firstBrace + 1);
                } else {
                    $jsonContent = trim($evaluationReport);
                }
            }
            
            // Clean control characters (except newline, carriage return, and tab) that might break json_decode
            $jsonContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $jsonContent);
            
            // Remove trailing commas before closing brackets/braces (common LLM error)
            $jsonContent = preg_replace('/,\s*([\]}])/m', '$1', $jsonContent);
            
            $evaluationData = json_decode($jsonContent, true);
            
            // If initial parse failed, try progressively more aggressive cleaning
            if (!is_array($evaluationData)) {
                // Fix unescaped newlines/tabs inside JSON string values
                // Replace literal newlines and tabs with their escaped equivalents
                $cleaned = preg_replace_callback(
                    '/"(?:[^"\\\\]|\\\\.)*(?:"|$)/s',
                    function ($match) {
                        $str = $match[0];
                        // Replace actual newlines/tabs inside quoted strings with escaped versions
                        $str = str_replace(["\r\n", "\r", "\n", "\t"], ["\\n", "\\n", "\\n", "\\t"], $str);
                        return $str;
                    },
                    $jsonContent
                );
                $evaluationData = json_decode($cleaned, true);
                // Update jsonContent so it doesn't trigger Control Character errors when stored/rendered raw
                $jsonContent = $cleaned;
            }
            
            $decodeSuccess = is_array($evaluationData);

            if ($decodeSuccess) {
                // Trim all keys to avoid issues with LLMs strictly copying spaces from prompt templates
                $trimKeys = function($array) use (&$trimKeys) {
                    $result = [];
                    foreach ($array as $key => $value) {
                        $result[trim($key)] = is_array($value) ? $trimKeys($value) : $value;
                    }
                    return $result;
                };
                $evaluationData = $trimKeys($evaluationData);
            }
            
            // Proceed to save the evaluation report
            if ($decodeSuccess) {
                // Use JSON_INVALID_UTF8_SUBSTITUTE to prevent json_encode failure
                $encoded = json_encode($evaluationData, JSON_INVALID_UTF8_SUBSTITUTE);
                if ($encoded !== false) {
                    $evaluationReport = $encoded;
                } else {
                    $evaluationReport = json_encode($evaluationData, JSON_INVALID_UTF8_SUBSTITUTE) ?: $jsonContent;
                }
            }
            
            $isJson = $decodeSuccess;

            $studentName = $isJson ? ($evaluationData['learner_name'] ?? $evaluationData['student_name'] ?? 'Unknown Learner') : 'Unknown Learner';
            $assignmentName = $isJson ? ($evaluationData['assignment_name'] ?? 'Unknown Assignment') : 'Unknown Assignment';
            $learnerEmail = $isJson ? ($evaluationData['learner_email'] ?? $evaluationData['email'] ?? null) : null;
            $moduleName = $isJson ? ($evaluationData['module_name'] ?? $evaluationData['module'] ?? null) : null;

            $evaluation = AIAssignmentEvaluation::create([
                'student_name' => $studentName,
                'learner_email' => $learnerEmail,
                'assignment_name' => $assignmentName,
                'module_name' => $moduleName,
                'prompt_file' => $promptPath ?? 'saved_prompt',
                'saved_prompt_id' => $request->saved_prompt_id,
                'zip_file' => $zipPath,
                'evaluation_report' => $evaluationReport,
                'status' => 'completed',
            ]);

            Log::info('AI assignment evaluation saved', [
                'evaluation_id' => $evaluation->id,
                'is_json_response' => $isJson,
            ]);

            // Clear memory: Delete extracted files to save disk space
            Storage::deleteDirectory($storageFolder . '/extracted');

            return redirect()->route('ai.evaluator')->with('latest_evaluation_id', $evaluation->id);
        } catch (\Exception $e) {
            Log::error('AI Assignment processing failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Unable to process assignment evaluation. Please try again.');
        }
    }

    public function show(AIAssignmentEvaluation $evaluation)
    {
        $repairedJson = $this->repairTruncatedJson($evaluation->evaluation_report);
        $evaluationData = json_decode($repairedJson, true);
        $isJson = json_last_error() === JSON_ERROR_NONE;
        
        if (!$isJson) {
            $evaluation->evaluation_report = "JSON Error: " . json_last_error_msg() . "\n\n" . $evaluation->evaluation_report;
        } else {
            $evaluation->evaluation_report = $repairedJson; // pass repaired string to view
        }

        return view('ai-evaluator.result', compact('evaluation', 'isJson', 'evaluationData'));
    }

    public function downloadPdf(AIAssignmentEvaluation $evaluation)
    {
        $content = $this->formatEvaluationContent($evaluation);
        
        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', sprintf('attachment; filename="ai-evaluation-%s.txt"', $evaluation->id));
    }

    public function downloadDocx(AIAssignmentEvaluation $evaluation)
    {
        $docx = $this->generateDocx($evaluation);
        
        return response($docx, 200)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', sprintf('attachment; filename="ai-evaluation-%s.docx"', $evaluation->id))
            ->header('Content-Length', strlen($docx));
    }

    public function downloadJson(AIAssignmentEvaluation $evaluation)
    {
        return response($evaluation->evaluation_report)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', sprintf('attachment; filename="ai-evaluation-%s.json"', $evaluation->id));
    }

    protected function generateDocx(AIAssignmentEvaluation $evaluation): string
    {
        $tmpDir = sys_get_temp_dir() . '/docx_' . Str::random(12);
        @mkdir($tmpDir, 0777, true);

        try {
            // Create required directory structure
            @mkdir($tmpDir . '/word', 0777, true);
            @mkdir($tmpDir . '/_rels', 0777, true);
            @mkdir($tmpDir . '/word/_rels', 0777, true);

            // Create document.xml content
            $content = $this->formatEvaluationContent($evaluation);
            $escapedContent = htmlspecialchars($content, ENT_XML1, 'UTF-8');

            $documentXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <w:body>
    <w:p>
      <w:pPr>
        <w:pStyle w:val="Heading1"/>
      </w:pPr>
      <w:r>
        <w:rPr>
          <w:b/>
          <w:sz w:val="48"/>
        </w:rPr>
        <w:t>AI Assignment Evaluation Report</w:t>
      </w:r>
    </w:p>
    <w:p>
      <w:pPr/>
    </w:p>
XML;

            $lines = array_filter(explode("\n", $escapedContent));
            foreach ($lines as $line) {
                $documentXml .= sprintf(<<<'XML'
    <w:p>
      <w:pPr/>
      <w:r>
        <w:rPr/>
        <w:t xml:space="preserve">%s</w:t>
      </w:r>
    </w:p>
XML
                , $line);
            }

            $documentXml .= <<<'XML'
  </w:body>
</w:document>
XML;

            file_put_contents($tmpDir . '/word/document.xml', $documentXml);

            // Create [Content_Types].xml
            $contentTypes = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>
XML;
            file_put_contents($tmpDir . '/[Content_Types].xml', $contentTypes);

            // Create _rels/.rels
            $rels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML;
            file_put_contents($tmpDir . '/_rels/.rels', $rels);

            // Create word/_rels/document.xml.rels
            $docRels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
</Relationships>
XML;
            file_put_contents($tmpDir . '/word/_rels/document.xml.rels', $docRels);

            // Create ZIP archive
            $zip = new \ZipArchive();
            $zipPath = sys_get_temp_dir() . '/evaluation_' . $evaluation->id . '.docx';
            
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create DOCX file');
            }

            // Add files to ZIP with proper structure
            $zip->addFile($tmpDir . '/[Content_Types].xml', '[Content_Types].xml');
            $zip->addFile($tmpDir . '/_rels/.rels', '_rels/.rels');
            $zip->addFile($tmpDir . '/word/document.xml', 'word/document.xml');
            $zip->addFile($tmpDir . '/word/_rels/document.xml.rels', 'word/_rels/document.xml.rels');

            $zip->close();

            // Read the generated DOCX
            $docxContent = file_get_contents($zipPath);

            // Cleanup
            array_map('unlink', glob($tmpDir . '/word/_rels/*'));
            array_map('unlink', glob($tmpDir . '/word/*'));
            array_map('unlink', glob($tmpDir . '/_rels/*'));
            array_map('unlink', glob($tmpDir . '/*'));
            @rmdir($tmpDir . '/word/_rels');
            @rmdir($tmpDir . '/word');
            @rmdir($tmpDir . '/_rels');
            @rmdir($tmpDir);
            @unlink($zipPath);

            return $docxContent;
        } catch (\Exception $e) {
            Log::error('DOCX generation failed', ['message' => $e->getMessage()]);
            
            // Fallback to plain text if DOCX generation fails
            return $this->formatEvaluationContent($evaluation);
        }
    }

    protected function buildAIPrompt(string $promptContent, string $submissionContent, string $zipFilename): string
    {
        $template = trim(<<<'PROMPT'
====================================================
ASSIGNMENT PROMPT FILE (YOUR GRADING BIBLE)
====================================================
The following document contains the FULL assignment instructions provided by the instructor.
You MUST read it completely and extract:
  - Every task / section with its EXACT name
  - The marks / percentage allocated to each task
  - All required files, technologies, features, and deliverables
  - All business rules, validation rules, and coding standards

Do NOT invent, assume, or add any criteria that are not explicitly stated in this document.
Do NOT rename tasks. Use the EXACT task names and mark values from this document.

--- BEGIN ASSIGNMENT PROMPT FILE ---
{PROMPT_INSTRUCTIONS}
--- END ASSIGNMENT PROMPT FILE ---

====================================================
LEARNER SUBMISSION (EXTRACTED FROM ZIP)
====================================================
Original ZIP Filename: {ZIP_FILENAME}
The following content was extracted from the learner's submitted ZIP file.
Each file is clearly labeled with ===== FILE: filename ===== markers.
Evaluate the learner's work in each file against the assignment requirements above.

--- BEGIN LEARNER SUBMISSION ---
{STUDENT_SUBMISSION}
--- END LEARNER SUBMISSION ---

====================================================
FINAL INSTRUCTION
====================================================
1. Extract the Learner's Name from the original ZIP filename or the submission files. If you cannot find a name, use "Unknown Learner".
2. Extract the Assignment Number/Name from the original ZIP filename or the submission files. If you cannot find it, use "Unknown Assignment".
3. Extract the Learner's Email ID from the submission files. If you cannot find it, use null.
4. Extract the Module Name from the submission files or filename. If you cannot find it, use null.
5. Evaluate the learner's work strictly according to the criteria defined in the Grading Prompt.
6. If a file is marked as [STATUS: File exists but content is binary/PDF/DOC and cannot be displayed in text mode...], assume it is physically PRESENT and valid for submission checks.
7. If a file shows "PDF content could not be parsed automatically.", the file EXISTS but its text could not be extracted (likely image-based PDF or encrypted). Assume the file is present. Grade based on any surrounding context clues, and award partial credit where applicable.
8. Return ONLY a valid JSON object. No markdown. No extra text. Ensure the root of the JSON object includes the keys "learner_name", "assignment_name", "learner_email", and "module_name".

# JSON Output Format:
{
  "student_name": "Extracted student name",
  "assignment_name": "Extracted assignment name",
  "learner_email": "Extracted email id",
  "module_name": "Extracted module name",
  "grading_criteria": [
    {
      "criteria": "Name of the criteria being evaluated",
      "feedback": "Detailed feedback explaining how well the student met the criteria.",
      "fixing": "Actionable steps or suggestions for improvement.",
      "score": "The score awarded out of the maximum possible (e.g., '8 / 10' or 'Pass/Fail')"
    }
  ],
  "summary": {
    "max_score": "Total possible points",
    "earned_score": "Total points earned",
    "ksa_index": "Calculated score as a percentage (0 to 100)"
  },
  "action_plan": "A paragraph summarizing the main areas for improvement."
}
PROMPT
        );

        return str_replace([
            '{ZIP_FILENAME}',
            '{PROMPT_INSTRUCTIONS}',
            '{STUDENT_SUBMISSION}',
        ], [
            $zipFilename,
            $promptContent,
            $submissionContent,
        ], $template);
    }

    protected function formatEvaluationContent(AIAssignmentEvaluation $evaluation): string
    {
        $data = json_decode($evaluation->evaluation_report, true);
        $isJson = json_last_error() === JSON_ERROR_NONE;

        if (!$isJson) {
            return $evaluation->evaluation_report;
        }

        $output = [];

        // 1. Output Grading Criteria
        $criteriaList = [];
        if (isset($data['grading_criteria']) && is_array($data['grading_criteria'])) {
            $criteriaList = $data['grading_criteria'];
        } elseif (isset($data['criteria_feedback']) && is_array($data['criteria_feedback'])) {
            foreach ($data['criteria_feedback'] as $key => $val) {
                $val['criteria'] = ucwords(str_replace('_', ' ', $key));
                $criteriaList[] = $val;
            }
        }

        if (!empty($criteriaList)) {
            foreach ($criteriaList as $item) {
                $criteriaName = trim($item['criteria'] ?? 'N/A');
                // Remove internal whitespaces from score (e.g., "23 / 25" -> "23/25")
                $scoreVal = trim(str_replace(' ', '', $item['score'] ?? '0'));
                
                $output[] = $criteriaName;
                $output[] = "☑ Feedback";
                $output[] = trim($item['feedback'] ?? 'No feedback provided.');
                $output[] = "💡 Suggestion";
                $output[] = trim($item['fixing'] ?? 'No suggestion provided.');
                $output[] = "★ Score " . $scoreVal;
                $output[] = ""; // empty line separator
            }
        }

        // 2. Output Summary
        if (isset($data['summary'])) {
            $sum = $data['summary'];
            $maxScore = $sum['max_score'] ?? '100';
            $earnedScore = $sum['earned_score'] ?? '0';
            
            $ksaVal = floatval($sum['ksa_index'] ?? 0);
            if ($ksaVal <= 1.0 && $ksaVal > 0) {
                $ksaVal = $ksaVal * 100;
            }

            if ($ksaVal >= 80) {
                $ksaText = "Above 80 – Highly Competent Performance";
            } elseif ($ksaVal >= 60) {
                $ksaText = "60 to 80 – Competent Performance";
            } else {
                $ksaText = "Below 60 – Developing Performance";
            }

            $output[] = "☑ Summary";
            $output[] = "Max Score: " . $maxScore;
            $output[] = "Earned Score: " . $earnedScore;
            $output[] = "KSA Index: " . $ksaText;
            $output[] = ""; // empty line
        }

        // 3. Output Action Plan
        if (isset($data['action_plan'])) {
            $output[] = "🎯 Action Plan";
            $output[] = ""; // empty line
            
            $actionPlan = trim($data['action_plan']);
            // Robust regex to split the plan by sentences into neat list items
            $sentences = preg_split('/(?<=[.?!])\s+/', $actionPlan);
            $sentences = array_filter(array_map('trim', $sentences));
            
            foreach ($sentences as $sentence) {
                if (!empty($sentence)) {
                    $output[] = $sentence;
                }
            }
        }

        return implode("\n", $output);
    }

    protected function sanitizeFilename(string $filename): string
    {
        $clean = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        return Str::substr($clean, 0, 120);
    }

    protected function repairTruncatedJson(string $json): string
    {
        // Try to decode first; if successful, return as-is
        if (json_decode($json) !== null) {
            return $json;
        }

        // Clean any stray literal newlines/tabs from truncated string first
        $json = preg_replace_callback(
            '/"(?:[^"\\\\]|\\\\.)*(?:"|$)/s',
            function ($match) {
                $str = $match[0];
                $str = str_replace(["\r\n", "\r", "\n", "\t"], ["\\n", "\\n", "\\n", "\\t"], $str);
                return $str;
            },
            $json
        ) ?? $json;

        $len = strlen($json);
        $inString = false;
        $escaped = false;
        $stack = [];

        for ($i = 0; $i < $len; $i++) {
            $c = $json[$i];
            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($c === '\\') {
                    $escaped = true;
                } elseif ($c === '"') {
                    $inString = false;
                }
            } else {
                if ($c === '"') {
                    $inString = true;
                } elseif ($c === '{' || $c === '[') {
                    $stack[] = $c;
                } elseif ($c === '}') {
                    if (!empty($stack) && end($stack) === '{') array_pop($stack);
                } elseif ($c === ']') {
                    if (!empty($stack) && end($stack) === '[') array_pop($stack);
                }
            }
        }

        if ($inString) {
            $json .= '"';
        }

        while (!empty($stack)) {
            $c = array_pop($stack);
            if ($c === '{') {
                $json .= '}';
            } elseif ($c === '[') {
                $json .= ']';
            }
        }

        return $json;
    }

    public function list()
    {
        $evaluations = AIAssignmentEvaluation::orderBy('created_at', 'desc')->get();
        return response()->json($evaluations);
    }

    public function destroy(AIAssignmentEvaluation $evaluation)
    {
        $evaluation->delete();
        return response()->json(['message' => 'Evaluation deleted successfully']);
    }
}
