<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
class ZipArchive
{
    /**
     * @since 0.12
     */
    public static function zip(
        string $source,
        string $destination,
    ): bool {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();

        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files
                = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($source),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
            foreach ($files as $file => $fileData) {
                $file = str_replace('\\', '/', $file);

                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    /**
     * @since 0.12
     */
    public static function unzip(
        string $source,
        string $destination,
    ): bool {
        if (!extension_loaded('zip') || !is_file($source)) {
            return false;
        }

        $zip = new \ZipArchive();

        if ($zip->open($source) !== true) {
            return false;
        }

        try {
            if (!is_dir($destination)) {
                if (!mkdir($destination, 0775, true) && !is_dir($destination)) {
                    return false;
                }
            }

            $destinationReal = realpath($destination);

            if ($destinationReal === false) {
                return false;
            }

            $destinationReal = rtrim($destinationReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);

                if ($entryName === false) {
                    return false;
                }

                $entryName = str_replace('\\', '/', $entryName);

                if (
                    $entryName === ''
                    || str_contains($entryName, "\0")
                    || str_starts_with($entryName, '/')
                    || preg_match('#^[a-zA-Z]:/#', $entryName)
                    || str_contains($entryName, '../')
                    || str_contains($entryName, '/..')
                    || $entryName === '..'
                ) {
                    return false;
                }

                // Symlinks nicht entpacken
                $opsys = 0;
                $attributes = 0;

                if ($zip->getExternalAttributesIndex($i, $opsys, $attributes)) {
                    $mode = ($attributes >> 16) & 0xF000;

                    if ($mode === 0xA000) {
                        return false;
                    }
                }

                $targetPath = $destinationReal . str_replace('/', DIRECTORY_SEPARATOR, $entryName);

                if (str_ends_with($entryName, '/')) {
                    if (!is_dir($targetPath)) {
                        if (!mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
                            return false;
                        }
                    }

                    continue;
                }

                $targetDirectory = dirname($targetPath);

                if (!is_dir($targetDirectory)) {
                    if (!mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
                        return false;
                    }
                }

                $targetDirectoryReal = realpath($targetDirectory);

                if (
                    $targetDirectoryReal === false
                    || !str_starts_with(
                        rtrim($targetDirectoryReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
                        $destinationReal
                    )
                ) {
                    return false;
                }

                if (is_link($targetPath)) {
                    return false;
                }

                $sourceStream = $zip->getStream($entryName);

                if ($sourceStream === false) {
                    return false;
                }

                $targetStream = fopen($targetPath, 'wb');

                if ($targetStream === false) {
                    fclose($sourceStream);
                    return false;
                }

                $bytes = stream_copy_to_stream($sourceStream, $targetStream);

                if ($bytes === false) {
                    fclose($sourceStream);
                    fclose($targetStream);
                    return false;
                }

                fclose($sourceStream);
                fclose($targetStream);
            }

            return true;
        } finally {
            $zip->close();
        }
    }
}