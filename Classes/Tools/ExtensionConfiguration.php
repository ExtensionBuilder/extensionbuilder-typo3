<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class ExtensionConfiguration
{

    public static function read(
        string $pathToConfigurationJson,
    ): array {
        $return = [];
        $extensionJsonList = Tools\Folder::scanForFile($pathToConfigurationJson, 'json');
        foreach ($extensionJsonList ?? [] as $json) {
            $jsonData = Tools\Json::read($pathToConfigurationJson . '/' . $json);
            if (($jsonData ?? false)) {
				Tools\ConfigArray::arrayMerge($return, $jsonData);
            }
        }
        return $return;
    }

    public static function write(
        string $pathToConfigurationJson,
        string $fileName,
        array $arrayForJson,
    ): void {
        GeneralUtility::mkdir_deep($pathToConfigurationJson);
        Tools\Json::write(
            $pathToConfigurationJson . DIRECTORY_SEPARATOR
            . $fileName,
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
        $vendorJsonList = Tools\Folder::scanForFile($pathToConfigurationJson, 'json');
        foreach ($vendorJsonList ?? [] as $json) {
			$returnArray[] = $pathToConfigurationJson.$json;
        }
        return $returnArray;
    }

}