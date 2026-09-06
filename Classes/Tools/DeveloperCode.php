<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
class DeveloperCode
{
    /**
     * @since 0.12
     */
    public static function searchAndReplace(
        string $vendorName,
        string $extensionName,
        string $vendorNameOrg,
        string $extensionNameOrg,
        string $search,
        string $replace,
    ): void {
        // ToDo functionality test
        $pathToVendorsAndExtensions
            = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR
            . 'extensionbuilder_typo3' . DIRECTORY_SEPARATOR
            . 'VendorsAndExtensions' . DIRECTORY_SEPARATOR;

        $pathToVendorsAndExtensionsSouceNew
            = $pathToVendorsAndExtensions
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'DeveloperCode';

        $pathToVendorsAndExtensionsSouceOrg
            = $pathToVendorsAndExtensions
            . $vendorNameOrg . DIRECTORY_SEPARATOR
            . $extensionNameOrg . DIRECTORY_SEPARATOR
            . 'DeveloperCode';

        if (!file_exists($pathToVendorsAndExtensionsSouceOrg)) {
            return;
        }

        $directory = new \RecursiveDirectoryIterator($pathToVendorsAndExtensionsSouceNew);
        $iterator  = new \RecursiveIteratorIterator($directory);
        $fileListe = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($fileListe ?? [] as $filePathName => $fileArray) {
            $handle = @fopen($filePathName, 'r');
            $content = stream_get_contents($handle);
            fclose($handle);
            $content = str_replace($search, $replace, $content);
            $handle = @fopen($filePathName, 'w');
            @fwrite($handle, $content);
            fclose($handle);
        }
    }
}