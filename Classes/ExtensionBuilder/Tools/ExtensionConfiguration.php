<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class ExtensionConfiguration
{

    public static function read(
        string $pathToConfigurationJson,
    ): array {
        $returnArray = [];
        $extensionJsonList = Tools\Folder::scanFolderForFile($pathToConfigurationJson, 'json');
        foreach ($extensionJsonList ?? [] as $json) {
            $jsonData = Tools\Json::read($pathToConfigurationJson . '/' . $json);
            if (($jsonData ?? false)) {
				Tools\ConfigArray::arrayMerge($returnArray, $jsonData);
            }
        }
        return $returnArray;
    }

    public static function write(
        string $pathToConfigurationJson,
        string $jsonFileName,
        array $arrayForJson,
    ): void {
        GeneralUtility::mkdir_deep($pathToConfigurationJson);
        Tools\Json::write(
            $pathToConfigurationJson . DIRECTORY_SEPARATOR . $jsonFileName,
            $arrayForJson,
        );
    }

    static function writeSub(
        string $sub,
        string $pathToConfigurationJson,
        string $jsonFileName,
        array $arrayForJson,
    ): void {
        $tmpArrayForJson = [];
        $tmpArrayForJson[$sub] = $arrayForJson;
        self::write($pathToConfigurationJson, $jsonFileName, $tmpArrayForJson);
    }

    static function getExtensionFiles(
        string $pathToConfigurationJson,
    ): array {		
        $returnArray = [];	
        $vendorJsonList = Tools\Folder::scanFolderForFile($pathToConfigurationJson, 'json');
        foreach ($vendorJsonList ?? [] as $json) {
			$returnArray[] = $pathToConfigurationJson.$json;
        }
        return $returnArray;
    }

}