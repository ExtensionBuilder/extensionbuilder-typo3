<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Folder
{

    static function scanForDirectory(
        string $path,
        string $filter = '',
    ): array {
        $return = [];

        foreach (self::scanContent($path) ?? [] as $folderContent) {
            if (is_dir($path . DIRECTORY_SEPARATOR . $folderContent)) {
                if ($filter) {
                    if (strpos($path . DIRECTORY_SEPARATOR . $folderContent, $filter) != false) {
                        $return[] = $folderContent;
                    }
                } else {
                    $return[] = $folderContent;
                }
            }
        }

        return $return;
    }

    static function scanForDirectoryRecursive(
        array &$folder,
        string $path,
    ): array {
        $folderDirectory = FolderTools::scanForDirectory($path);

        foreach ($folderDirectory ?? [] as $folderName) {
            $folder[$folderName] = [];
            $folder[$folderName] =
                self::scanForDirectoryRecursive($folder[$folderName] , $path . DIRECTORY_SEPARATOR . $folderName);
        }

        return $folder;
    }

    static function scanForDirectoryRecursiveForFile(
        array &$folder,
        string $path,
    ): void {
        foreach ($folder ?? [] as $folderName => $folderData) {
			$files = self::scanForFile($path . DIRECTORY_SEPARATOR . $folderName);
			foreach ($files ?? [] as $fileName) {
				$folder[$folderName][$fileName] = $path . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR . $fileName;
			}
			if (is_array($folder[$folderName])) {
			    self::scanForDirectoryRecursiveForFile($folder[$folderName], $path . DIRECTORY_SEPARATOR . $folderName);
			}
        }
    }

	static function scanForFile(
        string $path,
        string $extensionFilter = '',
        string $filter = '',
    ): array {
        $return = [];

        $returnFolderContent = self::scanContent($path);
        foreach (self::scanContent($path) ?? [] as $folderContent) {
            if (is_file($path . DIRECTORY_SEPARATOR . $folderContent)) {
                $found = true;

                if ($filter) {
                    if (strpos($folderContent, $filter) === false) {
                        $found = false;
                    }
				}

                if ($extensionFilter && $found) {
					$pathinfo = pathinfo($path . DIRECTORY_SEPARATOR . $folderContent);
					if (!(($pathinfo['extension'] ?? '') === $extensionFilter)) {
                        $found = false;
                    }
                }

                if ($found) {
                    $return[] = $folderContent;
                }
            }
        }

        return $return;
    }

    static function scanContent(
        string $path,
    ): array {
        $return = [];

        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (($folderContent = readdir($handle)) !== false) {
                    if ($folderContent != '.' && $folderContent != '..') {
                        $return[] = $folderContent;
                    }
                }
                closedir($handle);
                asort($return, SORT_STRING);
            }
        }

        return $return;
    }

    static function deleteForFile(
        string $path,
        string $filter = '',
    ): void {
// ToDo Improve filter see function scanFor File
        $returnFile = [];

        $returnFolderContent = self::scanContent($path);
        foreach (self::scanContent($path) ?? [] as $folderContent) {
            if (is_file($path . DIRECTORY_SEPARATOR . $folderContent)) {
                if (strlen($filter) > 0) {
                    if (strpos($path . DIRECTORY_SEPARATOR . $folderContent, $filter) != false) {
                        unlink($path . DIRECTORY_SEPARATOR . $folderContent);
                    }
                } else {
                    unlink($path . DIRECTORY_SEPARATOR . $folderContent);
                }
            }
        }
    }

    static function copy(
        string $source,
        string $target,
    ): void {
        if (!file_exists($source)) { return; }

        GeneralUtility::mkdir_deep($target);
        $dir = opendir($source);
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    self::copy($source . '/' . $file, $target . '/' . $file);
                } else {
                    copy($source . '/' . $file, $target . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    static function delete(
        string $foldserToDelete,
    ): void {
		if (!file_exists($foldserToDelete)) { return; }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($foldserToDelete),
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file => $fileData) {
            $file = str_replace('\\', '/', $file);
            // Ignore '.' and '..' folders
            if (in_array(substr($file, strrpos($file, '/') +1), ['.', '..'])) { continue; }

            if (is_file($file)) { unlink($file); }
            if (is_dir($file)) { rmdir($file); }
		}
        if (is_file($foldserToDelete)) { unlink($foldserToDelete); }
        if (is_dir($foldserToDelete)) { rmdir($foldserToDelete); }
    }

}