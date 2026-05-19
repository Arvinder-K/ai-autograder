<?php
$zip = new \ZipArchive();
$path = "storage/app/private/uploads/ai-evaluator/20260516085504-5rsjVKDs/extracted/IU5_Assignment5_EAINT/A05_PROJECT_REPORT_EAINT_NADI_MAUNG.docx";
if ($zip->open($path) === true) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        if (strpos($filename, 'word/media/') === 0) {
            echo "Found media: " . $filename . "\n";
        }
    }
    $zip->close();
}
