<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
class File
{
    /**
     * @since 0.12
     */
    public static function count(
        string $path,
    ): array {

        $fileCount = 0;
        $fileCountByExtension = [];

        $lineCount = 0;
        $lineCountByExtension = [];

        // Nur bestimmte Dateitypen zählen (optional)
        $allowedExtensions = [
            'php',
            'html',
            'js',
            'css',
            'ts',
            'json',
            'yaml',
            'typoscript',
            'md',
            'rst',
            'txt',
            'cfg',
            'sql',
        ];

        foreach ($allowedExtensions as $allowedExtensionsAs => $extension) {
            $fileCountByExtension[$extension] = 0;
            $lineCountByExtension[$extension] = 0;
        }

        //$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path),RecursiveDirectoryIterator::SKIP_DOTS);
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }

            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

            // Nur bestimmte Endungen berücksichtigen
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }

            $fileCountByExtension[$extension]++;
            $fileCount++;

            // Zähle Zeilen
            $lines = count(file($file->getPathname()));
            $lineCount += $lines;
            $lineCountByExtension[$extension] += $lines;
        }

        return [];
    }
}