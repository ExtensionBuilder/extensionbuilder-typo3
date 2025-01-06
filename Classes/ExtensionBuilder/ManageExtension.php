<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use ExtensionBuilder\ExtensionbuilderTypo3Core\BuildExtensionCore;

class ManageExtension
{

    public array $vendorsAndExtensions = [];
    public array $vendorList = [];
    public array $extensionsList = [];
    public array $foreignExtensionsList = [];

    public array $config = [];
    public array $userConfig = [];
    public array $projects = [];

    public function __construct()
    {
        $this->vendorsAndExtensions = self::readExtensions();
        $this->vendorList = self::getVendorList();
        $this->extensionsList = self::getExtensionList();
        $this->foreignExtensionsList = self::getForeignExtensionList();
    }

    public function readExtensions(): array
    {
        $extensionsFolder = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();

        $tmpVendorsAndExtensions = [];

        $extensionsPath =
            Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;

        $extensionsList = Tools\Folder::scanFolderForDirectory($extensionsPath);

        // Get Vendors from directory
        $vendorList = Tools\Folder::scanFolderForDirectory($extensionsFolder);

        foreach ($vendorList ?? [] as $vendorKey => $vendorName) {

            // Read Vendordata
            $vendorJsonList = Tools\Folder::scanFolderForFile(
                $extensionsFolder . DIRECTORY_SEPARATOR
                . $vendorName, 'json'
            );
            foreach ($vendorJsonList ?? [] as $json) {

                $jsonData = Tools\Json::read(
                    $extensionsFolder . DIRECTORY_SEPARATOR
                    . $vendorName . DIRECTORY_SEPARATOR
                    . $json
                );
                if ($jsonData ?? false) {
                    if ($jsonData['vendor'] ?? false) {

                        $mdAlgo = 'sha512';

                        $vendorHash = hash($mdAlgo, $jsonData['vendor']['vendorName'] ?? '');
                        if (!($jsonData['vendor']['vendorNameHash'] ?? false)) {
                            $jsonData['vendor']['vendorNameHash'] = $mdAlgo . ':' . $vendorHash;
                        }
						
                        if (!($jsonData['vendor']['vendorId'] ?? false)) {
                            $jsonData['vendor']['vendorId'] = md5(uniqid((string)mt_rand(), true));
                            $jsonData['vendor']['vendorIdHash'] = $mdAlgo . ':' . hash($mdAlgo, $jsonData['vendor']['vendorId'] ?? '');
                        }

                        if (!($tmpVendorsAndExtensions[$vendorName] ?? false)) {
                            $tmpVendorsAndExtensions[$vendorName] = [];
                        }
                        $tmpVendorsAndExtensions[$vendorName] = $jsonData['vendor'];

                        // Create extension list
                        $extensionList = Tools\Folder::scanFolderForDirectory($extensionsFolder . DIRECTORY_SEPARATOR . $vendorName);
                        foreach ($extensionList ?? [] as $extensionKey => $extensionName) {

                            // Read Extension
                            if (!($tmpVendorsAndExtensions[$vendorName]['extensions'] ?? false)) {
                                $tmpVendorsAndExtensions[$vendorName]['extensions'] = [];
                            }

                            $tmpVendorsAndExtensions[$vendorName]['extensions'][$extensionName] = [];

                            $extensionPath =
                                $extensionsFolder . DIRECTORY_SEPARATOR
                                . $vendorName . DIRECTORY_SEPARATOR
                                . $extensionName . DIRECTORY_SEPARATOR;

                            $tmpVendorsAndExtensions[$vendorName]['extensions'][$extensionName] =
                                Tools\ExtensionConfiguration::read($extensionPath);

                            if (!($tmpVendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['type'] ?? false)) {
                                $tmpVendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['type'] = "extension";
                            }

//                            foreach ($tmpVendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['depends'] ?? [] as $dependsName => $dependsData) {
// ToDo
//echo "depend: ".$dependsName."<br />";

//                                $tmpDepends =
//                                    Tools\ExtensionConfiguration::read($extensionsPath.$dependsName, 'extensionbuilderexport.json');

//if ($extension === "extensionbuilder_core") {
//}

//if (!($tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions'] ?? false)) {
//	$tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions'] = [];
//}
//                                Tools\ConfigArray::arrayMerge(
//                                   $tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions'],
//                                   $tmpDepends,
//                                   $tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions']
//                                );

//if ($extension === "extensionbuilder_core") {
//}


//                                $tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions'] = 
//                                    Tools\ExtensionConfiguration::read($extensionsPath.$dependsName, 'extensionbuilder.json');
//                            }

                        }

                    }
				} else {
                    if (!($tmpVendorsAndExtensions[$vendorName] ?? false)) {
                        $tmpVendorsAndExtensions[$vendorName] = [];
                    }
                    $tmpVendorsAndExtensions[$vendorName]['jsonErrorInFile'][$json] = $jsonData['JsonError'];
				}
            }	
        }        
        return $tmpVendorsAndExtensions;
    }
	
    public function writeExtension(
        string $vendorName = '',
        string $extensionName = '',
    ): void {
        if ($vendorName) {

            $vendor = $this->vendorsAndExtensions[$vendorName];
            $vendorPath = Tools\ExtensionbuilderFolder::GetVendorsAndExtensionsBaseFolder();
            $vendorPath .= DIRECTORY_SEPARATOR . $vendorName;
            GeneralUtility::mkdir_deep($vendorPath);
            $vendorDataForJson = [];
            $vendorDataForJson['vendor'] = [];

            $vendorDataForJson['vendor'] = $vendor;
            unset($vendorDataForJson['vendor']['extensions']);

            Tools\ExtensionConfiguration::write(
                $vendorPath,
                'vendor.json',
                $vendorDataForJson
            );
			
    		if ($extensionName) {
                $extension = $vendor['extensions'][$extensionName];

                $extensionPath = $vendorPath . DIRECTORY_SEPARATOR . $extensionName;
                GeneralUtility::mkdir_deep($extensionPath);

                $devArray = [];

                $devCode = $extensionPath . DIRECTORY_SEPARATOR . 'DeveloperCode';
                $devArray[] = $devCode;

                $devCodeClasses = $devCode . DIRECTORY_SEPARATOR . 'Classes';
                $devArray[] = $devCodeClasses;
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Authentication';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Controller';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Domain';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . 'Finishers';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . 'Model';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . 'Renderer';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . 'Repository';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'EventListener';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Hooks';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Middleware';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Property';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Report';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Status';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Utility';

                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'ViewHelpers';

                $devCodeConfiguration = $devCode . DIRECTORY_SEPARATOR . 'Contribution';

                $devCodeConfiguration = $devCode . DIRECTORY_SEPARATOR . 'Configuration';
                $devArray[] = $devCodeConfiguration;
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Backend';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Extbase';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Extbase' . DIRECTORY_SEPARATOR . 'Persistence';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Flexforms';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Form';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Elements';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Finishers';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Validators';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Variants';

                $devCodeConfigurationSets = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Sets';
                $devArray[] = $devCodeConfigurationSets;
                $devArray[] = $devCodeConfigurationSets . DIRECTORY_SEPARATOR . 'SitePackage';
                $devArray[] = $devCodeConfigurationSets . DIRECTORY_SEPARATOR . 'SitePackage' . DIRECTORY_SEPARATOR . 'PageTsConfig';
                $devArray[] = $devCodeConfigurationSets . DIRECTORY_SEPARATOR . 'SitePackage' . DIRECTORY_SEPARATOR . 'TypoScript';

                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'RTE';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'TCA';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'TCA' . DIRECTORY_SEPARATOR . 'Overrides';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'TsConfig';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'TsConfig' . DIRECTORY_SEPARATOR . 'Page';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'TypoScript';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'TypoScript' . DIRECTORY_SEPARATOR . 'ContentElement';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . '';

                $devCodeClasses = $devCode . DIRECTORY_SEPARATOR . 'Contribution';

                $devArray[] = $devCode . DIRECTORY_SEPARATOR . 'Documentation';

                $devCodeResources = $devCode . DIRECTORY_SEPARATOR . 'Resources';
                $devArray[] = $devCodeResources;

                $devCodeResourcesPrivate = $devCodeResources . DIRECTORY_SEPARATOR . 'Private';
                $devArray[] = $devCodeResourcesPrivate;
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Language';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Layouts';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'ContentElements';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'Page';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Partials';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Partials' . DIRECTORY_SEPARATOR . 'ContentElements';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Partials' . DIRECTORY_SEPARATOR . 'Page';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Templates';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'ContentElements';
                $devArray[] = $devCodeResourcesPrivate . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'Page';

                $devCodeResourcesPublic = $devCodeResources . DIRECTORY_SEPARATOR . 'Public';
                $devArray[] = $devCodeResourcesPublic;
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'Css';
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'Fonts';
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'Icons';
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'Images';
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'JavaScript';
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'Scss';
                $devArray[] = $devCodeResourcesPublic . DIRECTORY_SEPARATOR . 'Scss' . DIRECTORY_SEPARATOR . 'Theme';


                foreach ($devArray ?? [] as $devArrayaKey => $devArrayData) {
                    GeneralUtility::mkdir_deep($devArrayData);
                }

                // .json Delete files to prevent duplicates
                Tools\Folder::deleteFolderForFile($extensionPath, '.json');
				
                if (!($extension['extensionBuild'] ?? false)) { $extension['extensionBuild'] = []; }

                $extension['extensionBuild']['editorVersion'] =
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');

                foreach ($extension ?? [] as $extensionDataName => $extensionData) {
                    if (is_array($extensionData)) {

                        switch ($extensionDataName) {
                            case 'tables':
                                foreach ($extensionData ?? [] as $tableName => $table) {

                                    if ($table['columns'] ?? false) {
                                        $extensionDataForJson = [];
                                        $extensionDataForJson[$tableName] = [];
                                        $extensionDataForJson[$tableName]['columns'] = [];									
                                        $extensionDataForJson[$tableName]['columns'] = $table['columns'];
                                        Tools\ExtensionConfiguration::writeSub(
                                            'tables',
                                            $extensionPath,
                                            'table.' . $tableName . '.2.columns.json',
                                            $extensionDataForJson );
                                        unset($table['columns']);
									}

                                    if ($table['controller'] ?? false) {
                                        $extensionDataForJson = [];
                                        $extensionDataForJson[$tableName] = [];
                                        $extensionDataForJson[$tableName]['controller'] = [];									
                                        $extensionDataForJson[$tableName]['controller'] = $table['controller'];
                                        Tools\ExtensionConfiguration::writeSub(
                                            'tables',
                                            $extensionPath,
                                            'table.' . $tableName . '.3.controller.json',
                                            $extensionDataForJson );
                                        unset($table['controller']);
									}

                                    if ($table['tabs'] ?? false) {
                                        $extensionDataForJson = [];
                                        $extensionDataForJson[$tableName] = [];
                                        $extensionDataForJson[$tableName]['tabs'] = [];									
                                        $extensionDataForJson[$tableName]['tabs'] = $table['tabs'];
                                        Tools\ExtensionConfiguration::writeSub(
                                            'tables',
                                            $extensionPath,
                                            'table.' . $tableName . '.4.tabs.json',
                                            $extensionDataForJson
                                        );
                                        unset($table['tabs']);
									}

                                    if ($table['palettes'] ?? false) {
                                        $extensionDataForJson = [];
                                        $extensionDataForJson[$tableName] = [];
                                        $extensionDataForJson[$tableName]['palettes'] = [];									
                                        $extensionDataForJson[$tableName]['palettes'] = $table['palettes'];
                                        Tools\ExtensionConfiguration::writeSub(
                                            'tables',
                                            $extensionPath,
                                            'table.' . $tableName . '.5.palettes.json',
                                            $extensionDataForJson
                                        );
                                        unset($table['palettes']);
									}

                                    $extensionDataForJson = [];
                                    $extensionDataForJson[$tableName] = $table; // ToDo array  merge?
                                    Tools\ExtensionConfiguration::writeSub(
                                        'tables',
                                        $extensionPath,
                                        'table.' . $tableName . '.1.json',
                                        $extensionDataForJson
                                    );
                                }
                                break;

                            case 'enumerations':
                                foreach ($extensionData ?? [] as $enumerationName => $enumeration) {
                                    $extensionDataForJson = [];
                                    $extensionDataForJson[$enumerationName] = $enumeration; // ToDo array  merge?
                                    Tools\ExtensionConfiguration::writeSub(
                                        'enumerations',
                                        $extensionPath,
                                        'enumeration.' . $enumerationName . '.json',
                                        $extensionDataForJson
                                    );
                                }
                                break;

                            default:
                                $extensionDataForJson = [];
                                $extensionDataForJson[$extensionDataName] = $extensionData; // ToDo array  merge?
                                Tools\ExtensionConfiguration::write(
                                    $extensionPath,
                                    $extensionDataName . '.json',
                                    $extensionDataForJson
                                );
						}
                    } 
                }
		    }
		}
    }
	
    public function deleteExtension(
        string $vendorName,
        string $extensionName,
    ): void {

		if (($this->vendorsAndExtensions[$vendorName] ?? false)) {
            $vendorFolder = Tools\ExtensionbuilderFolder::GetVendorsAndExtensionsBaseFolder() . DIRECTORY_SEPARATOR . $vendorName;
		    if ($extensionName) {
		        if ($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] ?? []) {
                    $extensionsFolder = $vendorFolder . DIRECTORY_SEPARATOR . $extensionName;
                    unset($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]);
                    Tools\Folder::deleteFolder($extensionsFolder);
    			}
		    }
        }
	}	

    public function renameVendor(
        string $vendorName = '',
        string $vendorNameNew = '',
    ): void {
//ToDo
        if ($this->vendorsAndExtensions[$vendorName] ?? false) {

            $vendorFolder = Tools\ExtensionbuilderFolder::GetVendorsAndExtensionsBaseFolder() . DIRECTORY_SEPARATOR . $vendorName;
            $vendorFolderNew = Tools\ExtensionbuilderFolder::GetVendorsAndExtensionsBaseFolder() . DIRECTORY_SEPARATOR . $vendorNameNew;
		
// Tools\Folder::deleteFolder($pathToExtensionFolder);

        }
	}

    public function renameExtension(
        string $vendorName = '',
        string $extensionName = '',
        string $extensionNameNew = '',
    ): void {
//ToDo
echo 'rename: '.$vendorName.'<br />';
echo 'rename: '.$extensionName.'<br />';
echo 'rename: '.$extensionNameNew.'<br />';

        if ($this->vendorsAndExtensions[$vendorName] ?? false) {

		}		
	}

    public function copyExtension(
        string $vendorName = '',
        string $extensionName = '',
        string $extensionNameNew = '',
    ): void {
//ToDo
echo 'rename: '.$vendorName.'<br />';
echo 'rename: '.$extensionName.'<br />';
echo 'rename: '.$extensionNameNew.'<br />';

        if ($this->vendorsAndExtensions[$vendorName] ?? false) {

		}		
	}

    // private function

    private function getEbConfig(): array
    {
        $configPath = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();
        $returnArray = [];
        $returnArray = Tools\Json::read($configPath . 'extensionbuilder.json');
        $returnArray = $returnArray['config'] ?? [];
        $returnArray['extensionVersion'] =
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');
        return $returnArray;
    }

    private function getUserConfig(): array
    {
        $configPath = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();
        $returnArray = [];
        $returnArray = Tools\Json::read($configPath . $GLOBALS['BE_USER']->user['username'] . '.json');
        $returnArray = $returnArray['config'] ?? [];
        return $returnArray;
    }

    private function getProjects(): array
    {
        $configPath = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();
        $returnArray = [];
        $returnArray = Tools\Json::read($configPath . 'projects.json');
        $returnArray = $returnArray['projects'] ?? [];
        return $returnArray;
    }

    public function getVendorList(): array
	{
        $returnArray = [];
        foreach ($this->vendorsAndExtensions ?? [] as $vendorName => $vendorData) {
			$returnArray[] = $vendorData['vendorName'] ?? 'Erro: No vemdorname';
		}
        return $returnArray;
	}

    public function getExtensionList(): array
	{
        $returnArray = [];
        $extensionsPath =
            Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;
        $extensionsList = Tools\Folder::scanFolderForDirectory($extensionsPath);
        foreach ($extensionsList ?? [] as $extensionsName) {
// ToDo weiter daten einlesen z.b. Vendor, Vesion..
            $returnArray[$extensionsName] = [];
		}
        return $returnArray;
	}

    public function getForeignExtensionList(): array
	{
        // Duchsucht die Extenions nach extension_builder_export.json und liest disen ein und gibt eine Array zurück.

        // ToDo nur depencs ext laden

        $returnArray = [];

        $extensionsPath =
            Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;

        foreach ($this->extensionsList ?? [] as $extensionsName => $extensionsData) {
            $tmpFile =
                $extensionsPath . DIRECTORY_SEPARATOR
                . $extensionsName . DIRECTORY_SEPARATOR
                . 'extension_builder_export.json';
            if (file_exists($tmpFile)) {
                $jsonData = Tools\Json::read($tmpFile);
                if (($jsonData ?? false)) {
                    $jsonData = $jsonData ?? []; // Knoten entfernen
				    Tools\ConfigArray::arrayMerge($returnArray, $jsonData);
                }
	        }
		}
        return $returnArray;
	}

    protected function getVedorData(
        string $vendorName,
    ): array {
        $returnArray = [];
        foreach ($this->vendorsAndExtensions[$vendorName] ?? [] as $vendorData) {
            if (!is_array($vendorData)) {
                $returnArray[] = $vendorData;
            }
        }
        return $returnArray;
    }

}