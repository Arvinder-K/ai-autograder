<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class FileReaderService
{
    protected array $allowedExtensions = [
        // Python
        'py',
        // PHP / Laravel
        'php',
        // Web
        'js', 'ts', 'jsx', 'tsx', 'vue', 'html', 'css', 'scss',
        // Java
        'java',
        // C# / .NET
        'cs',
        // Ruby
        'rb',
        // Database
        'sql',
        // Config / Data
        'json', 'xml', 'yaml', 'yml', 'env',
        // Text / Docs
        'md', 'txt', 'docx', 'doc', 'pdf',
        // Shell
        'sh', 'bat',
        // Other
        'kt', 'swift', 'go', 'r',
    ];

    protected array $ignoreDirectories = [
        'vendor',
        'node_modules',
        '__pycache__',
    ];

    public function readPromptFile(string $path, ?string $originalFilename = null): string
    {
        $extension = strtolower(pathinfo($originalFilename ?? $path, PATHINFO_EXTENSION));

        if ($extension === 'docx' || $extension === 'doc') {
            return $this->readDocx($path);
        }

        if ($extension === 'pdf') {
            return $this->readPdf($path);
        }

        return $this->readTextFile($path);
    }

    public function readExtractedFiles(string $directory): string
    {
        // Increase PCRE and Memory limits for large PDF/DOCX parsing
        ini_set('pcre.backtrack_limit', '10000000');
        ini_set('pcre.recursion_limit', '10000000');
        ini_set('memory_limit', '512M');

        $files = $this->listAllowedFiles($directory);
        $output = [];

        Log::info('Reading extracted files for evaluation', [
            'directory' => $directory,
            'file_count' => count($files)
        ]);

        foreach ($files as $file) {
            $relativePath = Str::replaceFirst($directory . DIRECTORY_SEPARATOR, '', $file);
            $content = $this->readFile($file);

            // Even if content is "failed", we still want to show the file exists
            if ($content === '' || $content === 'PDF content could not be parsed automatically.' || $content === 'DOC content could not be parsed automatically.') {
                $output[] = sprintf("===== FILE: %s =====\n[STATUS: File exists but content extraction failed. Please check if this file is mentioned in other documents or if you can infer its content from its filename.]\n%s", $relativePath, $content);
                continue;
            }

            $output[] = sprintf("===== FILE: %s =====\n%s", $relativePath, $content);
        }

        return trim(implode("\n\n", $output));
    }

    protected function listAllowedFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $regex = new RegexIterator($iterator, '/^.+\.(' . implode('|', $this->allowedExtensions) . ')$/i', RecursiveRegexIterator::GET_MATCH);

        $files = [];

        foreach ($regex as $fileMatch) {
            $filePath = $fileMatch[0];
            if ($this->shouldIgnoreDirectory($filePath)) {
                continue;
            }
            $files[] = $filePath;
        }

        sort($files);

        return $files;
    }

    protected function shouldIgnoreDirectory(string $path): bool
    {
        $normalized = strtolower(str_replace(['\\', '/'], '/', $path));

        foreach ($this->ignoreDirectories as $ignoreDirectory) {
            if (str_contains($normalized, '/' . $ignoreDirectory . '/') || str_starts_with($normalized, $ignoreDirectory . '/')) {
                return true;
            }
        }

        return false;
    }

    protected function readFile(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'docx' || $extension === 'doc') {
            return $this->readDocx($path);
        }

        if ($extension === 'pdf') {
            return $this->readPdf($path);
        }

        return $this->readTextFile($path);
    }

    protected function readTextFile(string $path): string
    {
        try {
            $content = file_get_contents($path);
        } catch (\Exception $e) {
            Log::warning('Unable to read text file', ['path' => $path, 'message' => $e->getMessage()]);
            return '';
        }

        return $this->sanitizeContent($content);
    }

    protected function readDocx(string $path): string
    {
        try {
            $content = '';
            $zip = new \ZipArchive();
            // Try to open as DOCX (ZIP format)
            if ($zip->open($path) === true) {
                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    $xml = $zip->getFromIndex($index);
                    $xml = str_replace(['</w:p>', '</w:r>', '<w:tab/>'], ["\n", " ", "\t"], $xml);
                    $content = strip_tags($xml);
                }
                $zip->close();
                return $this->sanitizeContent($content);
            }
            
            // Fallback for older .doc (binary format)
            $rawContent = file_get_contents($path);
            
            // Remove null bytes first (in case of UTF-16LE strings)
            $rawContent = str_replace("\x00", "", $rawContent);
            
            // Extract printable strings of length 4 or more
            if (preg_match_all('/[\x20-\x7E\x0A\x0D\x09]{4,}/', $rawContent, $matches)) {
                $content = implode("\n", $matches[0]);
                $content = preg_replace('/\n{3,}/', "\n\n", $content);
                
                // Limit length to avoid blowing up the AI token limit
                if (strlen($content) > 20000) {
                    $content = substr($content, 0, 20000) . "\n... [CONTENT TRUNCATED] ...";
                }
            } else {
                $content = 'DOC content could not be parsed automatically.';
            }
            
            return $this->sanitizeContent($content);
        } catch (\Exception $e) {
            Log::error('Error reading DOC file', ['path' => $path, 'error' => $e->getMessage()]);
            return 'DOC content could not be parsed automatically.';
        }
    }

    protected function readPdf(string $path): string
    {
        try {
            // 1. Try to use Smalot PdfParser (Manual Load)
            $libPath = app_path('Libraries/pdfparser-2.11.0/autoload.php');
            if (file_exists($libPath)) {
                require_once $libPath;
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($path);
                    $text = $pdf->getText();
                    
                    if (trim($text) !== '' && count(explode(' ', $text)) > 10) {
                        return $this->sanitizeContent($text);
                    }
                } catch (\Exception $e) {
                    Log::warning('Smalot PDFParser failed, falling back', ['path' => $path, 'error' => $e->getMessage()]);
                }
            }

            // 2. Fallback to our robust manual extractor
            $raw = file_get_contents($path);
            if ($raw === false || strlen($raw) < 10) {
                return 'PDF content could not be parsed automatically.';
            }

            $text = $this->extractTextFromPdfContent($raw);

            // If extracted text is mostly garbage, try the Super Fallback
            $cleaned = preg_replace('/\s+/', ' ', $text);
            $words = array_filter(explode(' ', $cleaned), fn($w) => strlen($w) >= 3 && preg_match('/[a-zA-Z]{2,}/', $w));

            if (count($words) < 10) {
                // 3. Super Fallback: Extract all printable ASCII strings (4+ chars) from raw binary
                // This can often recover text from broken/weird PDF structures
                Log::warning('Standard PDF extraction yielded insufficient content, trying Super Fallback', ['path' => $path]);
                
                $strings = [];
                if (preg_match_all('/[\x20-\x7E]{4,}/', $raw, $matches)) {
                    foreach ($matches[0] as $match) {
                        // Skip strings that look like PDF operators or binary junk
                        if (preg_match('/[a-zA-Z]/', $match) && !preg_match('/^[\/<>\[\]()0-9\s.]+$/', $match)) {
                            $strings[] = $match;
                        }
                    }
                }
                
                if (count($strings) > 10) {
                    $text = implode(' ', $strings);
                } else {
                    return 'PDF content could not be parsed automatically.';
                }
            }

            $result = $text;
            if (strlen($result) > 30000) {
                $result = substr($result, 0, 30000) . "\n... [CONTENT TRUNCATED] ...";
            }

            return $this->sanitizeContent($result);
        } catch (\Exception $e) {
            Log::warning('Unable to read PDF file', ['path' => $path, 'message' => $e->getMessage()]);
        }

        return 'PDF content could not be parsed automatically.';
    }

    protected function extractTextFromPdfContent(string $raw): string
    {
        $allText = [];

        // Split into individual PDF objects to check their type
        // We find stream start/end markers along with their preceding dictionary
        $pattern = '/(<<[^>]*>>)\s*stream([\s\S]*?)endstream/U';
        preg_match_all($pattern, $raw, $objects, PREG_SET_ORDER);

        foreach ($objects as $obj) {
            $dict   = $obj[1];
            $stream = $obj[2];

            // Skip image, font binary, and XObject streams (they contain binary garbage)
            if (preg_match('/\/Subtype\s*\/Image/i', $dict)) continue;
            if (preg_match('/\/Type\s*\/XObject/i', $dict) && !preg_match('/\/Subtype\s*\/Form/i', $dict)) continue;

            // Trim leading newline from stream
            $stream = ltrim($stream, "\r\n");

            // Try to decompress: most modern PDFs use FlateDecode (zlib/deflate)
            $decoded = null;
            if (preg_match('/\/Filter\s*\/FlateDecode/i', $dict) || preg_match('/\/Filter\s*\[\/FlateDecode\]/i', $dict)) {
                // Try gzuncompress (zlib with header)
                $decoded = @gzuncompress($stream);
                if ($decoded === false) {
                    // Try gzinflate (raw deflate, no header)
                    $decoded = @gzinflate($stream);
                }
                if ($decoded === false) {
                    // Try stripping first 2 bytes (zlib header) before inflate
                    $decoded = @gzinflate(substr($stream, 2));
                }
            }

            $streamText = $this->extractTextFromRawStream($decoded !== false && $decoded !== null ? $decoded : $stream);
            if (trim($streamText) !== '') {
                $allText[] = $streamText;
            }
        }

        // Fallback: if no stream text found, do a raw scan of the whole file
        if (empty($allText)) {
            $fallback = $this->extractTextFromRawStream($raw);
            if (trim($fallback) !== '') {
                $allText[] = $fallback;
            }
        }

        return implode("\n", $allText);
    }

    protected function extractTextFromRawStream(string $stream): string
    {
        $parts = [];

        // Strategy 1: TJ operator — [(text) offset (text)] TJ
        if (preg_match_all('/\[([^\]]*)\]\s*TJ/s', $stream, $m)) {
            foreach ($m[1] as $block) {
                // Extract string literals inside parens
                if (preg_match_all('/\(((?>\\\\.|[^()\\\\])*)\)/', $block, $sm)) {
                    foreach ($sm[1] as $s) {
                        $parts[] = $this->decodePdfString($s);
                    }
                }
                // Extract hex strings <AABB..>
                if (preg_match_all('/<([0-9A-Fa-f]{2,})>/', $block, $hm)) {
                    foreach ($hm[1] as $hex) {
                        $parts[] = $this->decodeHexString($hex);
                    }
                }
                $parts[] = ' ';
            }
        }

        // Strategy 2: Tj operator — (text) Tj
        if (preg_match_all('/\(((?>\\\\.|[^()\\\\])*)\)\s*Tj/s', $stream, $m)) {
            foreach ($m[1] as $s) {
                $parts[] = $this->decodePdfString($s) . ' ';
            }
        }

        // Strategy 3: Newline-aware text operators (quote operators)
        if (preg_match_all('/\(((?>\\\\.|[^()\\\\])*)\)\s*[\'"]/', $stream, $m)) {
            foreach ($m[1] as $s) {
                $parts[] = "\n" . $this->decodePdfString($s);
            }
        }

        // Strategy 4: Hex strings from TJ if no literal parens found
        if (empty(array_filter($parts, fn($p) => strlen(trim($p)) > 0))) {
            if (preg_match_all('/<([0-9A-Fa-f]{4,})>/', $stream, $m)) {
                foreach ($m[1] as $hex) {
                    $decoded = $this->decodeHexString($hex);
                    if (strlen(trim($decoded)) > 1) {
                        $parts[] = $decoded . ' ';
                    }
                }
            }
        }

        // Join and clean
        $text = implode('', $parts);
        // Remove excess whitespace
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]+/', ' ', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }

    /**
     * Decode PDF string escape sequences into readable text
     */
    protected function decodePdfString(string $s): string
    {
        // Handle PDF escape sequences: \n \r \t \b \f \( \) \\ and octal \ddd
        $s = preg_replace_callback('/\\\\([nrtbf()\\\\]|[0-7]{1,3})/', function ($m) {
            $ch = $m[1];
            if (is_numeric($ch[0])) return chr(octdec($ch));
            return match ($ch) {
                'n'  => "\n",
                'r'  => "\r",
                't'  => "\t",
                'b'  => "\x08",
                'f'  => "\x0C",
                '('  => '(',
                ')'  => ')',
                '\\' => '\\',
                default => $ch,
            };
        }, $s);

        // Strip non-printable chars
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $s);
        return $s;
    }

    /**
     * Decode a hex-encoded PDF string to readable text
     */
    protected function decodeHexString(string $hex): string
    {
        // Pad to even length
        if (strlen($hex) % 2 !== 0) $hex .= '0';
        $bytes = hex2bin($hex);
        if ($bytes === false) return '';

        // If looks like UTF-16BE (starts with BOM or has many null bytes), convert
        if (strlen($bytes) >= 2 && $bytes[0] === "\xFE" && $bytes[1] === "\xFF") {
            $bytes = mb_convert_encoding(substr($bytes, 2), 'UTF-8', 'UTF-16BE');
        } elseif (preg_match('/^(\x00[\x20-\x7E])+$/', $bytes)) {
            $bytes = mb_convert_encoding($bytes, 'UTF-8', 'UTF-16BE');
        }

        // Strip non-printable chars
        return preg_replace('/[\x00-\x1F\x7F]/', '', $bytes);
    }

    protected function sanitizeContent(string $content): string
    {
        // 1. Force UTF-8 and ignore malformed characters
        // This is the most robust way to ensure json_encode won't fail
        if (function_exists('iconv')) {
            $content = iconv('UTF-8', 'UTF-8//IGNORE', $content);
        } else {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }
        
        // 2. Strip control characters except newline and tab
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);

        // 3. Normalize whitespace
        $content = preg_replace('/\r\n|\r/', "\n", $content);
        $content = preg_replace('/\t+/', ' ', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }
}
