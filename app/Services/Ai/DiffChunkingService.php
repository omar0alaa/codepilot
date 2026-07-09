<?php

namespace App\Services\Ai;

/**
 * Diff Chunking Service
 * 
 * Handles large diffs by splitting them into manageable chunks
 * that fit within AI model token limits.
 */
class DiffChunkingService
{
    private const MAX_CHUNK_SIZE = 8000;

    /**
     * Split a large diff into chunks based on file boundaries
     */
    public function chunkDiff(string $diff): array
    {
        if (strlen($diff) <= self::MAX_CHUNK_SIZE) {
            return [$diff];
        }

        // Split diff by file boundaries (diff --git lines)
        $fileDiffs = $this->splitByFile($diff);
        
        $chunks = [];
        $currentChunk = '';
        
        foreach ($fileDiffs as $fileDiff) {
            // If a single file diff is too large, truncate it
            if (strlen($fileDiff) > self::MAX_CHUNK_SIZE) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }
                $chunks[] = substr($fileDiff, 0, self::MAX_CHUNK_SIZE) 
                    . "\n... [File diff truncated due to size]";
                continue;
            }

            // If adding this file would exceed chunk size, start a new chunk
            if (strlen($currentChunk . $fileDiff) > self::MAX_CHUNK_SIZE) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }
                $currentChunk = $fileDiff;
            } else {
                $currentChunk .= $fileDiff;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Split a diff string by file boundaries
     */
    private function splitByFile(string $diff): array
    {
        $pattern = '/^(diff --git .+)$/m';
        $parts = preg_split($pattern, $diff, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $files = [];
        for ($i = 0; $i < count($parts); $i += 2) {
            $header = $parts[$i] ?? '';
            $content = $parts[$i + 1] ?? '';
            $files[] = $header . $content;
        }

        return !empty($files) ? $files : [$diff];
    }

    /**
     * Get summary stats about a diff
     */
    public function getDiffStats(string $diff): array
    {
        $fileCount = preg_match_all('/^diff --git /m', $diff);
        $additions = preg_match_all('/^\+[^+]/m', $diff);
        $deletions = preg_match_all('/^-[^-]/m', $diff);

        return [
            'files' => $fileCount,
            'additions' => $additions,
            'deletions' => $deletions,
            'size' => strlen($diff),
            'chunks' => count($this->chunkDiff($diff)),
        ];
    }

    /**
     * Extract changed files from a diff
     */
    public function extractFiles(string $diff): array
    {
        $files = [];
        $pattern = '/^diff --git a\/(.+?) b\/(.+)$/m';
        
        if (preg_match_all($pattern, $diff, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $files[] = [
                    'old_path' => $match[1],
                    'new_path' => $match[2],
                ];
            }
        }

        return $files;
    }
}
