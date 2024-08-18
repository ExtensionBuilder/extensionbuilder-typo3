<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use ExtensionBuilder\ExtensionbuilderTypo3Core\BuildExtensionCore;

// ToDo
//
// public function getForeignExtensionList(): array

class ManageExtension
{

    public array $config = [];
    public array $userConfig = [];
    public array $projects = [];
    public array $currentProjects = [];
    public array $vendorsAndExtensions = [];
    public array $vendorList = [];
    public array $extensionsList = [];
    public array $foreignExtensionsList = [];

    public function __construct()
    {

        $this->vendorsAndExtensions = self::read();

        $this->currentProjects = self::getCurrentProjects(); // ToDo remove

        $this->vendorList = self::getVendorList();
        $this->extensionsList = self::getExtensionList();

        $this->foreignExtensionsList = self::getForeignExtensionList();

    }

    public function read(): array
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
            $vendorJsonList = Tools\Folder::scanFolderForFile($extensionsFolder . DIRECTORY_SEPARATOR . $vendorName, 'json');
            foreach ($vendorJsonList ?? [] as $json) {

                $jsonData = Tools\Json::read($extensionsFolder . DIRECTORY_SEPARATOR . $vendorName . DIRECTORY_SEPARATOR . $json);
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

                        // Extension Liste erstellen
                        $extensionList = Tools\Folder::scanFolderForDirectory($extensionsFolder . DIRECTORY_SEPARATOR . $vendorName);
                        foreach ($extensionList ?? [] as $extensionKey => $extensionName) {

                            // Extesion einlesen
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


                            foreach ($tmpVendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['depends'] ?? [] as $dependsName => $dependsData) {

//echo "depend: ".$dependsName."<br />";

//                                $tmpDepends =
//                                    Tools\ExtensionConfiguration::read($extensionsPath.$dependsName, 'extensionbuilderexport.json');

//if ($extension === "extensionbuilder_core") {
//debug($extensionsPath.$dependsName,"extensionbuilder_core");
//debug($tmpDepends,"extensionbuilder_core 1");	
//debug($tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['extension'],"extensionbuilder_core");		
//debug($tmpDepends,"extensionbuilder_core");	
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
//debug($tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions'],"extensionbuilder_core 2");
//}


//                                $tmpVendorsAndExtensions[$vendor]['extensions'][$extension]['dependsExtensions'] = 
//                                    Tools\ExtensionConfiguration::read($extensionsPath.$dependsName, 'extensionbuilder.json');
                            }

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

	
    public function write(
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
                $vendorDataForJson,
            );
			
    		if ($extensionName) {
                $extension = $vendor['extensions'][$extensionName];

                $extensionPath = $vendorPath . DIRECTORY_SEPARATOR . $extensionName;
                GeneralUtility::mkdir_deep($extensionPath);
				
                // .json Dateien löschen, um dupllten zu verhinden
                Tools\Folder::deleteFolderForFile($extensionPath, '.json');
				
//ToDo Backup vorm löschen der Daten

                if (!($extension['extensionBuild'] ?? false)) { $extension['extensionBuild'] = []; }

                $extension['extensionBuild']['editorVersion'] =
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion(Setup\GlobalConfig::EXT_NAME);

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
                                            'table.'.$tableName.'.4.tabs.json',
                                            $extensionDataForJson );
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
                                            $extensionDataForJson );
                                        unset($table['palettes']);
									}

                                    $extensionDataForJson = [];
                                    $extensionDataForJson[$tableName] = $table; // ToDo array  merge?
                                    Tools\ExtensionConfiguration::writeSub(
                                        'tables',
                                        $extensionPath,
                                        'table.' . $tableName . '.1.json',
                                        $extensionDataForJson,
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
                                        $extensionDataForJson,
                                    );
                                }
                                break;

                            default:
                                $extensionDataForJson = [];
                                $extensionDataForJson[$extensionDataName] = $extensionData; // ToDo array  merge?
                                Tools\ExtensionConfiguration::write(
                                    $extensionPath,
                                    $extensionDataName . '.json',
                                    $extensionDataForJson,
                                );
						}
                    } 
                }
		    }
		}
    }

	
    public function delete(
        string $vendorName = '',
        string $extensionName = '',
    ): void {
// ToDo Logging
		if (($this->vendorsAndExtensions[$vendorName] ?? false)) {
            $vendorFolder = Tools\ExtensionbuilderFolder::GetVendorsAndExtensionsBaseFolder() . DIRECTORY_SEPARATOR . $vendorName;
		    if ($extensionName) {
		        if (($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] ?? [])) {
                    $extensionsFolder = $vendorFolder . DIRECTORY_SEPARATOR . $extensionName;
                    unset($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]);
                    Tools\Folder::deleteFolder($extensionsFolder);				
    			}
		    } else {
                unset($this->vendorsAndExtensions[$vendorName]);
                Tools\Folder::deleteFolder($vendorFolder);
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

    public function getCurrentProjects(): array
    {
        $returnArray = [];
// ToDo
        $returnArray['extensions'] = []; 
        foreach ($this->vendorsAndExtensions ?? [] as $vendorName => $vendorData) {
            foreach ($vendorData['extensions'] ?? [] as $extensionName => $extensionData) {
				if (!is_array($extensionData['extensionBuild'] ?? '')) { continue; }
                if ($extensionData['extensionBuild']['currentProject'] ?? false) {
//                if (array_key_exists('currentProject', $extensionData['extensionBuild'] ?? [])) {
                    if ($extensionData['extensionBuild']['currentProject'] ?? false) {
                        $returnArray['extensions'][$extensionName] = $extensionData;
                    }
                }
            }
        }
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
            Environment::getProjectPath() . DIRECTORY_SEPARATOR .
            'typo3conf' . DIRECTORY_SEPARATOR .
            'ext' . DIRECTORY_SEPARATOR;

        foreach ($this->extensionsList ?? [] as $extensionsName => $extensionsData) {
            $tmpFile =
                $extensionsPath . DIRECTORY_SEPARATOR .
                $extensionsName . DIRECTORY_SEPARATOR .
                'extension_builder_export.json';
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