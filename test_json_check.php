<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FileReaderService;

echo "Checking JSON encoding of the evaluation payload...\n";

$folder = __DIR__.'/storage/app/private/uploads/ai-evaluator/20260518043521-CwOyu3Db';
$promptPath = $folder . '/A03_GreenCampus_AutoGrader_Prompt_Assignment3.docx';
$extractionPath = $folder . '/extracted';

$fileReader = new FileReaderService();
$promptContent = $fileReader->readPromptFile($promptPath, basename($promptPath));
$submissionContent = $fileReader->readExtractedFiles($extractionPath);

$studentName = "Kaung Shin Wai";
$assignmentName = "Green Campus Shuttle Ticket Kiosk";

$template = trim(<<<'PROMPT'
STUDENT: {STUDENT_NAME}
ASSIGNMENT: {ASSIGNMENT_NAME}
{PROMPT_INSTRUCTIONS}
{STUDENT_SUBMISSION}
PROMPT
);

$aiPrompt = str_replace([
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

$attachments = [];
$totalSize = 0;
$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractionPath));
foreach ($files as $file) {
    if ($file->isFile()) {
        $extension = strtolower($file->getExtension());
        if (in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            $attachments[] = $file->getRealPath();
        }
    }
}

$parts = [
    ['text' => $aiPrompt]
];

foreach ($attachments as $filePath) {
    if (file_exists($filePath)) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            default => null
        };
        if ($mimeType) {
            $parts[] = [
                'text' => "=== ATTACHMENT: " . basename($filePath) . " ==="
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

$data = [
    'contents' => [
        [
            'parts' => $parts
        ]
    ]
];

$json = json_encode($data);
if ($json === false) {
    echo "JSON ENCODING FAILED!\n";
    echo "Error Message: " . json_last_error_msg() . "\n";
    echo "Error Code: " . json_last_error() . "\n";
} else {
    echo "JSON ENCODING SUCCEEDED!\n";
    echo "Payload Size: " . strlen($json) . " bytes\n";
}
