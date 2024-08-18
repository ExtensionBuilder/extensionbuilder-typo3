<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;

class ExtensionbuilderFolder
{

	static function getExtensionbuilderPath(): string
	{
	    return
            Environment::getConfigPath() . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR
            . 'extensionbuilder_typo3';
	}

    static function getExtensionBuilderFolder(): string
    {
        $tmpReturn =
            Environment::getPublicPath() . DIRECTORY_SEPARATOR
            . 'fileadmin' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR;
        GeneralUtility::mkdir_deep($tmpReturn);
		return $tmpReturn;
    }

    static function getVendorsAndExtensionsBaseFolder(): string
    {
        $tmpReturn =
            Environment::getPublicPath() . DIRECTORY_SEPARATOR
            . 'fileadmin' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR;
        GeneralUtility::mkdir_deep($tmpReturn);
		return $tmpReturn;
    }

    static function getPathToTypo3conf(): string
    {
		return
            Environment::getConfigPath() . DIRECTORY_SEPARATOR;
    }

    static function getPathToTypo3confExt(): string
    {
		return
            Environment::getConfigPath() . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;
    }

    static function getPathToVendorsAndExtensionsSouce(
        string $vendorName,
        string $extensionName,
    ): string {
		return
            self::getVendorsAndExtensionsBaseFolder()
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR;
    }

    static function getPathToVendorsAndExtensionsTarget(
        string $vendorName,
        string $extensionName,
    ): string {
		return
            self::getPathToTypo3confExt()
            . 'extensionbuilder_typo3_core' . DIRECTORY_SEPARATOR
            . 'VendorsAndExtensions' . DIRECTORY_SEPARATOR
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR;
    }

    static function getPathToExtension(
        string $extensionName,
    ): string {
		return self::getPathToTypo3confExt() . $extensionName . DIRECTORY_SEPARATOR;
    }

}