<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Folder
{

    static function scanFolderForDirectory(
        string $path,
        string $filter = '',
    ): array {
        $returnFolder = [];

        foreach (self::scanFolderContent($path) ?? [] as $tmpFolderContent) {
            if (is_dir($path . DIRECTORY_SEPARATOR . $tmpFolderContent)) {
                if ($filter) {
                    if (strpos($path . DIRECTORY_SEPARATOR . $tmpFolderContent, $filter) != false) {
                        $returnFolder[] = $tmpFolderContent;
                    }
                } else {
                    $returnFolder[] = $tmpFolderContent;
                }
            }
        }

        return $returnFolder;
    }

    static function scanFolderForDirectoryRecursive(
        array &$folder,
        string $path,
    ): array {
        $folderDirectory = FolderTools::scanFolderForDirectory($path);

        foreach ($folderDirectory ?? [] as $folderName) {
            $folder[$folderName] = [];
            $folder[$folderName] =
                self::scanFolderForDirectoryRecursive($folder[$folderName] , $path . DIRECTORY_SEPARATOR . $folderName);
        }

        return $folder;
    }

    static function scanFolderForDirectoryRecursiveForFile(
        array &$folder,
        string $path,
    ): void {
        foreach ($folder ?? [] as $folderName => $folderData) {
			$files = self::scanFolderForFile($path . DIRECTORY_SEPARATOR . $folderName);
			foreach ($files ?? [] as $fileName) {
				$folder[$folderName][$fileName] = $path . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR . $fileName;
			}
			if (is_array($folder[$folderName])) {
			    self::scanFolderForDirectoryRecursiveForFile($folder[$folderName], $path . DIRECTORY_SEPARATOR . $folderName);
			}
        }
    }

	static function scanFolderForFile(
        string $path,
        string $extensionFilter = '',
        string $filter = '',
    ): array {
        $return = [];

        $returnFolderContent = self::scanFolderContent($path);
        foreach (self::scanFolderContent($path) ?? [] as $folderContent) {
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

    static function scanFolderContent(
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

    static function deleteFolderForFile(
        string $path,
        string $filter = '',
    ): void {
// ToDo Improve filter see function scanFolderFor File
        $returnFile = [];

        $returnFolderContent = self::scanFolderContent($path);
        foreach (self::scanFolderContent($path) ?? [] as $folderContent) {
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

    static function deleteFolder(
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