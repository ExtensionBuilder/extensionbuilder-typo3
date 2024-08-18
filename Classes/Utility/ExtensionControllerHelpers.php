<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Utility\ExtensionController;

class Helper
{

    static function save(
        string $vendorName,
        string $extensionName,
        array $extensionData,
    ): void {
        $extensionData['extension']['version']  = (string)($extensionData['extension']['versionMajor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionMinor'] ?? '1');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionRevision'] ?? '0');

        $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;
        $this->extensionbulderObject->write($vendorName, $extensionName);

        ModuleController::flashMessage('Vendor: ' . $vendorName, 'Saving extension: ' . $extensionName);
    }
	
}