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

        return view('ai-evaluator.index', compact('evaluations'));
    }

    public function process(AIAssignmentEvaluationRequest $request)
    {
        set_time_limit(300);
        try {
            $validated = $request->validated();

            $studentName = preg_replace('/[^\x20-\x7E\t\n\r\xA0-\xFF]/', '', trim(strip_tags($validated['student_name'])));
            $assignmentName = preg_replace('/[^\x20-\x7E\t\n\r\xA0-\xFF]/', '', trim(strip_tags($validated['assignment_name'])));

            $uploadFolder = sprintf('ai-evaluator/%s-%s', now()->format('YmdHis'), Str::random(8));
            $storageFolder = "uploads/{$uploadFolder}";

            Log::info('AI assignment upload received', [
                'student_name' => $studentName,
                'assignment_name' => $assignmentName,
                'upload_folder' => $storageFolder,
            ]);

            $promptFilename = $this->sanitizeFilename($request->file('prompt_file')->getClientOriginalName());
            $zipFilename = $this->sanitizeFilename($request->file('zip_file')->getClientOriginalName());

            $promptPath = Storage::putFileAs($storageFolder, $request->file('prompt_file'), $promptFilename);
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

            $promptContent = $this->fileReaderService->readPromptFile(
                $request->file('prompt_file')->getRealPath(),
                $request->file('prompt_file')->getClientOriginalName()
            );
            $submissionContent = $this->fileReaderService->readExtractedFiles($extractionPath);

            $aiPrompt = $this->buildAIPrompt($studentName, $assignmentName, $promptContent, $submissionContent);

            Log::info('AI prompt assembled', [
                'student_name' => $studentName,
                'assignment_name' => $assignmentName,
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
            
            // Robust JSON extraction: Find the first { and the last }
            $firstBrace = strpos($evaluationReport, '{');
            $lastBrace = strrpos($evaluationReport, '}');
            
            if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                $jsonContent = substr($evaluationReport, $firstBrace, $lastBrace - $firstBrace + 1);
            } else {
                $jsonContent = trim($evaluationReport);
            }
            
            // Basic cleanup for trailing commas (e.g. "key": "value", } -> "key": "value" })
            $jsonContent = preg_replace('/,\s*([\]}])/m', '$1', $jsonContent);
            
            $evaluationData = json_decode($jsonContent, true);
            
            // Inject original prompt content if parsing was successful
            if (is_array($evaluationData)) {
                $evaluationData['original_prompt_content'] = $promptContent;
                $evaluationReport = json_encode($evaluationData);
            }
            
            $isJson = json_last_error() === JSON_ERROR_NONE;

            $evaluation = AIAssignmentEvaluation::create([
                'student_name' => $studentName,
                'assignment_name' => $assignmentName,
                'prompt_file' => $promptPath,
                'zip_file' => $zipPath,
                'evaluation_report' => $evaluationReport,
                'status' => 'completed',
            ]);

            Log::info('AI assignment evaluation saved', [
                'evaluation_id' => $evaluation->id,
                'is_json_response' => $isJson,
            ]);

            return view('ai-evaluator.result', compact('evaluation', 'isJson', 'evaluationData'));
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
        $evaluationData = json_decode($evaluation->evaluation_report, true);
        $isJson = json_last_error() === JSON_ERROR_NONE;

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

    protected function buildAIPrompt(string $studentName, string $assignmentName, string $promptContent, string $submissionContent): string
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

STUDENT: {STUDENT_NAME}
ASSIGNMENT: {ASSIGNMENT_NAME}

--- BEGIN ASSIGNMENT PROMPT FILE ---
{PROMPT_INSTRUCTIONS}
--- END ASSIGNMENT PROMPT FILE ---

====================================================
STUDENT SUBMISSION (EXTRACTED FROM ZIP)
====================================================
The following content was extracted from the student's submitted ZIP file.
Each file is clearly labeled with ===== FILE: filename ===== markers.
Evaluate the student's work in each file against the assignment requirements above.

--- BEGIN STUDENT SUBMISSION ---
{STUDENT_SUBMISSION}
--- END STUDENT SUBMISSION ---

====================================================
FINAL INSTRUCTION
====================================================
- Grade ONLY based on what is found in the STUDENT SUBMISSION above.
- Use ONLY the criteria and marks defined in the ASSIGNMENT PROMPT FILE above.
- If a file is marked as [STATUS: File exists but content is binary/PDF/DOC and cannot be displayed in text mode...], assume it is physically PRESENT and valid for submission checks.
- If a file shows "PDF content could not be parsed automatically.", the file EXISTS but its text could not be extracted (likely image-based PDF or encrypted). Assume the file is present. Grade based on any surrounding context clues, and award partial credit where applicable.
- Return ONLY a valid JSON object. No markdown. No extra text.
PROMPT
        );

        return str_replace([
            '{STUDENT_NAME}',
            '{ASSIGNMENT_NAME}',
            '{PROMPT_INSTRUCTIONS}',
            '{STUDENT_SUBMISSION}',
        ], [
            $studentName,
            $assignmentName,
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
        if (isset($data['grading_criteria']) && is_array($data['grading_criteria'])) {
            foreach ($data['grading_criteria'] as $item) {
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
}
