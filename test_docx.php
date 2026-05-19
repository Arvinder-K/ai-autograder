<?php
$zip = new \ZipArchive();
$path = "storage/app/private/uploads/ai-evaluator/20260516085504-5rsjVKDs/extracted/IU5_Assignment5_EAINT/A05_PROJECT_REPORT_EAINT_NADI_MAUNG.docx";
if (file_exists($path)) {
    if ($zip->open($path) === true) {
        if (($index = $zip->locateName('word/document.xml')) !== false) {
            $xml = $zip->getFromIndex($index);
            echo "Found XML length: " . strlen($xml) . "\n";
            $xml = str_replace(['</w:p>', '</w:r>', '<w:tab/>'], ["\n", " ", "\t"], $xml);
            $content = strip_tags($xml);
            echo "Content length: " . strlen($content) . "\n";
            echo substr($content, 0, 500) . "\n";
        } else {
            echo "No word/document.xml\n";
        }
        $zip->close();
    } else {
        echo "Could not open ZIP\n";
    }
} else {
    echo "File not found\n";
}
