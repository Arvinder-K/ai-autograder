<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class ZipExtractorService
{
    protected array $ignoreDirectories = [
        'vendor',
        'node_modules',
        '__pycache__',
    ];

    protected array $dangerousExtensions = [
        'exe',
        'bat',
        'cmd',
        'sh',
        'msi',
        'com',
        'scr',
        'dll',
        'jar',
    ];

    public function extract(UploadedFile $zipFile, string $destination, int $fileLimit = 500): array
    {
        $zip = new ZipArchive();
        $opened = $zip->open($zipFile->getRealPath());

        if ($opened !== true) {
            Log::error('Unable to open ZIP archive', ['path' => $zipFile->getRealPath()]);
            throw new \RuntimeException('Unable to open ZIP archive.');
        }

        File::ensureDirectoryExists($destination);

        $extractedFiles = [];
        $destinationNormalized = $this->normalizePath($destination);

        for ($index = 0; $index < $zip->numFiles; $index++) {
            if (count($extractedFiles) >= $fileLimit) {
                $zip->close();
                throw new \RuntimeException('ZIP file contains too many files.');
            }

            $entryName = $zip->getNameIndex($index);
            if ($entryName === null || str_ends_with($entryName, '/')) {
                continue;
            }

            if ($this->shouldIgnoreEntry($entryName)) {
                Log::info('Skipping ignored ZIP entry', ['entry' => $entryName]);
                continue;
            }

            $safeEntry = $this->sanitizeEntryPath($entryName);

            if ($safeEntry === '') {
                Log::warning('ZIP entry sanitized to empty path', ['entry' => $entryName]);
                continue;
            }

            $targetPath = $destinationNormalized . DIRECTORY_SEPARATOR . $safeEntry;
            if (!$this->pathIsInDirectory($targetPath, $destinationNormalized)) {
                Log::warning('ZIP slip attempt blocked', ['entry' => $entryName]);
                continue;
            }

            $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
            if (in_array($extension, $this->dangerousExtensions, true)) {
                Log::warning('Skipping dangerous ZIP entry', ['entry' => $entryName]);
                continue;
            }

            File::ensureDirectoryExists(dirname($targetPath));
            $content = $zip->getFromIndex($index);
            if ($content === false) {
                continue;
            }

            File::put($targetPath, $content);
            $extractedFiles[] = $targetPath;
        }

        $zip->close();

        return $extractedFiles;
    }

    protected function shouldIgnoreEntry(string $entryName): bool
    {
        $normalizedEntry = strtolower(str_replace(['\\', '/'], '/', $entryName));

        foreach ($this->ignoreDirectories as $ignoreDirectory) {
            if (str_contains($normalizedEntry, '/' . $ignoreDirectory . '/') || 
                str_starts_with($normalizedEntry, $ignoreDirectory . '/') ||
                str_ends_with($normalizedEntry, '/' . $ignoreDirectory)) {
                return true;
            }
        }

        return false;
    }

    protected function sanitizeEntryPath(string $entryName): string
    {
        $entryName = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim($entryName, "\\/"));
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $entryName), function ($part) {
            return $part !== '' && $part !== '.' && $part !== '..';
        });

        $parts = array_map(function ($part) {
            return preg_replace('/[^A-Za-z0-9._-]/', '_', $part);
        }, $parts);

        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    protected function normalizePath(string $path): string
    {
        return rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    protected function pathIsInDirectory(string $path, string $directory): bool
    {
        $normalizedDirectory = $this->normalizePath($directory);
        $normalizedPath = $this->normalizePath($path);

        return str_starts_with($normalizedPath, $normalizedDirectory . DIRECTORY_SEPARATOR);
    }
}
