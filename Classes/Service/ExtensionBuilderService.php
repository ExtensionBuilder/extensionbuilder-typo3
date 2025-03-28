<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class ExtensionBuilderService implements SingletonInterface
{
    public array $vendorsAndExtensions = [];

    private string $vendorName;
    private string $extensionName;
    private array $configuration;
    private array $developer;

    public function __construct()
    {
        $this->vendorsAndExtensions = self::readVendorsAndExtensions();
    }

    private function readVendorsAndExtensions(): array
    {
        $extensionsFolder = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();
        $vendorsAndExtensions = [];

        // Get Vendors from directory
        $vendorList = Tools\Folder::scanForDirectory($extensionsFolder);

        foreach ($vendorList ?? [] as $vendorKey => $vendorName) {
            // Read Vendordata
            $vendorJsonList = Tools\Folder::scanForFile(
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

                        if (!($vendorsAndExtensions[$vendorName] ?? false)) {
                            $vendorsAndExtensions[$vendorName] = [];
                        }
                        $vendorsAndExtensions[$vendorName] = $jsonData['vendor'];

                        // Create extension list
                        $extensionList = Tools\Folder::scanForDirectory($extensionsFolder . DIRECTORY_SEPARATOR . $vendorName);
                        foreach ($extensionList ?? [] as $extensionKey => $extensionName) {

                            // Read Extension
                            if (!($vendorsAndExtensions[$vendorName]['extensions'] ?? false)) {
                                $vendorsAndExtensions[$vendorName]['extensions'] = [];
                            }

                            $vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = [];

                            $extensionPath =
                                $extensionsFolder . DIRECTORY_SEPARATOR
                                . $vendorName . DIRECTORY_SEPARATOR
                                . $extensionName . DIRECTORY_SEPARATOR;

                            $vendorsAndExtensions[$vendorName]['extensions'][$extensionName] =
                                Tools\ExtensionConfiguration::read($extensionPath);

                            if (!($vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['type'] ?? false)) {
                                $vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['type'] = "extension";
                            }
                        }
                    }
				} else {
                    if (!($vendorsAndExtensions[$vendorName] ?? false)) {
                        $vendorsAndExtensions[$vendorName] = [];
                    }
                    $vendorsAndExtensions[$vendorName]['jsonErrorInFile'][$json] = $jsonData['JsonError'];
				}
            }	
        }        
        return $vendorsAndExtensions;
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
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Command';
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
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Service';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Scheduler';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Status';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'Utility';
                $devArray[] = $devCodeClasses . DIRECTORY_SEPARATOR . 'ViewHelpers';

                $devCodeConfiguration = $devCode . DIRECTORY_SEPARATOR . 'Configuration';
                $devArray[] = $devCodeConfiguration;
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Backend';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Extbase';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'Extbase' . DIRECTORY_SEPARATOR . 'Persistence';
                $devArray[] = $devCodeConfiguration . DIRECTORY_SEPARATOR . 'FlexForms';
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

                $devArray[] = $devCode . DIRECTORY_SEPARATOR . 'Test';
                $devArray[] = $devCode . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'Functional';
                $devArray[] = $devCode . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'Unit';

                foreach ($devArray ?? [] as $devArrayaKey => $devArrayData) {
                    GeneralUtility::mkdir_deep($devArrayData);
                }

                // .json Delete files to prevent duplicates
                Tools\Folder::deleteForFile($extensionPath, '.json');
				
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
                    Tools\Folder::delete($extensionsFolder);
    			}
		    }
        }
	}


    public function writeExtensionPropoty(
                    string $vendorName,
                    string $extensionName,
                    string $propotysName,
                    string $propotyName,
                    array $propotyData,
    ): void {
            $extensionsFolder = 
				Tools\ExtensionbuilderFolder::GetVendorsAndExtensionsBaseFolder() . DIRECTORY_SEPARATOR
                . $vendorName . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR;

            $propotysData = $this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName][$propotysName] ?? [];

            $propotyDataForJson = [];
            $propotyDataForJson[$propotyName] = $propotyData;

		    Tools\ConfigArray::arrayMerge($propotysData, $propotyDataForJson);

            $propotyDataForJson = [];
            $propotyDataForJson[$propotysName] = $propotysData;
            Tools\ExtensionConfiguration::write(
                $extensionsFolder,
                $propotysName . '.json',
                $propotyDataForJson
            );
	}



    // Area for generating the extension

// 123456

    // ToDo Check if it works with the T3 composer version

    public function build(
        string $vendorName,
        string $extensionName,
        array $configuration,
        array $developer,
    ): void {
        $this->vendorName = $vendorName;
        $this->extensionName = $extensionName;
        $this->configuration = $configuration;
        $this->developer = $developer;

		$buildOk = false;

		if (
            ExtensionManagementUtility::isLoaded('extensionbuilder_typo3_core')
            && ($this->configuration['buildLocal'] ?? false)
        ) {
            $buildOk = self::buildLocal();
        } else {
            $buildOk = self::buildRemote();
        }

// ToDo composer composerVendorName composerExtensionName
$composerVendorName = strtolower($vendorName);
$composerExtensionName = strtolower($extensionName);

// ToDo
        // Extesion installieren (kopieren)
//        if ($buildOk && $copyInExtension) {
        if ($buildOk) {

            $buildPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . $configuration['varEb'] . DIRECTORY_SEPARATOR
                . $vendorName . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR
                . 'build' . DIRECTORY_SEPARATOR;

            if (Environment::isComposerMode()) {

                $extPath =
                    Environment::getProjectPath() . DIRECTORY_SEPARATOR
                    . 'vendor' . DIRECTORY_SEPARATOR
                    . $composerVendorName . DIRECTORY_SEPARATOR
                    . $composerExtensionName . DIRECTORY_SEPARATOR;
			} else {
                $extPath =
                    Environment::getPublicPath() . DIRECTORY_SEPARATOR
                    . 'typo3conf' . DIRECTORY_SEPARATOR
                    . 'ext' . DIRECTORY_SEPARATOR
                    . $extensionName . DIRECTORY_SEPARATOR;
			}

            GeneralUtility::rmdir($extPath, true);
            GeneralUtility::mkdir_deep($extPath);
            Tools\Folder::copy($buildPath, $extPath);

            if (Environment::isComposerMode()) {
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    '<ProjectPath>/vendor/' . $composerVendorName . DIRECTORY_SEPARATOR . $composerExtensionName,
                    'Successfully copy extensions files.',
                    ContextualFeedbackSeverity::OK,
                );
                $notificationQueue->enqueue($flashMessage);
            } else {
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    '<PublicPath>/typo3conf/ext/' . $extensionName,
                    'Successfully copy extensions files.',
                    ContextualFeedbackSeverity::OK,
                );
                $notificationQueue->enqueue($flashMessage);
            }

            if (!(ExtensionManagementUtility::isLoaded($this->extensionName))) {
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    '',
// ToDo LLL
                    'The created extension is not activated!',
                    ContextualFeedbackSeverity::WARNING,
                );
                $notificationQueue->enqueue($flashMessage);
                return;
		    }

// ToDo use for composer
            if ($this->developer['typo3']['maintenance']['flushT3andPhpCache'] ?? false) {
                $clearCacheService = GeneralUtility::makeInstance('TYPO3\\CMS\\Install\\Service\\ClearCacheService');
                $clearCacheService->clearAll();

                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    '',
// ToDo LLL
                    'Successfully cleared all caches and all available opcode caches.',
                    ContextualFeedbackSeverity::OK,
                );
                $notificationQueue->enqueue($flashMessage);
            }

// ToDo analyzeDatabaseStructure
            if ($this->developer['typo3']['maintenance']['analyzeDatabaseStructure'] ?? false) {

		    }

            if ($this->developer['typo3']['maintenance']['rebuildPhpAutoload'] ?? false) {
                if (!Environment::isComposerMode()) {
                    ClassLoadingInformation::dumpClassLoadingInformation();

                    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                    $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                    $flashMessage = GeneralUtility::makeInstance(
                        FlashMessage::class,
                        '',
// ToDo LLL
                        'Successfully dumped class loading information for extensions.',
                        ContextualFeedbackSeverity::OK,
                    );
                    $notificationQueue->enqueue($flashMessage);
                }
	         }
        } else {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                '',
// ToDo LLL
// ToDo Text
                'An error has occurred!',
                ContextualFeedbackSeverity::ERROR,
            );
            $notificationQueue->enqueue($flashMessage);
		}
	}

    private function buildLocal(): bool
    {

        // ToDo time measurement

        $return = true;

        $multipart = self::buildRequest();

        $buildCore = new \ExtensionBuilder\ExtensionbuilderTypo3Core\BuildExtensionCore($this->vendorName, $this->extensionName);

        $sourcePath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . $this->configuration['varEb'] . DIRECTORY_SEPARATOR
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR
            . 'source' . DIRECTORY_SEPARATOR;

        $sourcePathCore = $buildCore->pathes['source'];

        GeneralUtility::rmdir($sourcePathCore, true);
        GeneralUtility::mkdir_deep($sourcePathCore);
        Tools\Folder::copy($sourcePath, $sourcePathCore);

		$extConf = Tools\ExtensionConfiguration::read($sourcePathCore);

        // Erzeuge Extesnsion
        $buildStart = microtime(true);

        $buildCore->build($extConf);

        $buildDuration = microtime(true) - $buildStart;

        $buildPathCore = $buildCore->pathes['build'];
		
        $buildPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . $this->configuration['varEb'] . DIRECTORY_SEPARATOR
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR
            . 'build' . DIRECTORY_SEPARATOR;

        Tools\Folder::copy($buildPathCore, $buildPath);

        $debugPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . $this->configuration['varEb'] . DIRECTORY_SEPARATOR
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR
            . 'debug' . DIRECTORY_SEPARATOR;

        // Log in Json-Date schreiben
		GeneralUtility::mkdir_deep($debugPath);
        file_put_contents(
            $debugPath . 'bildLog.json',
            json_encode(($buildCore->extConf['buildLog'] ?? []), JSON_PRETTY_PRINT),
        );

//        if (($buildCore->buildLog['Usage'] ?? false) && $this->buildLogUsage0) {
//// ToDo
//            debug(
//                $usageX = \ExtensionBuilder\ExtensionbuilderTypo3\Tools\ConfigArray::removeUsage($buildCore->buildLog['Usage']),
//                'Build no usage'
//            );
//        }

//        if (($buildCore->buildLog['Usage'] ?? false) && $this->buildLogUsage) {
//// ToDo
//            debug($buildCore->buildLog['Usage'], 'Build usage');
//        }
//        if (($buildCore->buildLog['Info'] ?? false) && $this->buildLogInfo) {
//            debug($buildCore->buildLog['Info'], 'Build info');
//        }
//        if (($buildCore->buildLog['Warning'] ?? false) && $this->buildLogWarning) {
//            debug($buildCore->buildLog['Warning'], 'Build warning');
//        }
//        if (($buildCore->buildLog['ToDo'] ?? false) && $this->buildLogToDo) {
//            debug($buildCore->buildLog['ToDo'], 'Build todo');
//        }
//        if (($buildCore->buildLog['Error'] ?? false) && $this->buildLogError) {
//            debug($buildCore->buildLog['Error'], 'Build error');
//        }

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            'Extension: ' . $this->extensionName,
            'Extension is build (local).',
            ContextualFeedbackSeverity::OK,
        );
        $notificationQueue->enqueue($flashMessage);

        return $return;
	}

// 9999

    private function buildRemote(): bool
    {
        $buildOk = false;

        $multipart = self::buildRequest();

        $resultCode = Tools\RestApiClient::build(
            $this->configuration['builderUrl'],
            $this->configuration['builderApi'],
            $multipart,
        );

        switch ($resultCode['status'] ?? 'error') {
            case 'build OK':
                $debugPath =
                    Environment::getVarPath() . DIRECTORY_SEPARATOR
                    . $this->configuration['varEb'] . DIRECTORY_SEPARATOR
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'debug' . DIRECTORY_SEPARATOR;
                file_put_contents(
                    $debugPath . 'response.json',
                    json_encode($resultCode, JSON_PRETTY_PRINT)
                );
                $importPath =
                    Environment::getVarPath() . DIRECTORY_SEPARATOR
                    . $this->configuration['varEb'] . DIRECTORY_SEPARATOR
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'import' . DIRECTORY_SEPARATOR;
                $buildPath =
                    Environment::getVarPath() . DIRECTORY_SEPARATOR
                    . $this->configuration['varEb'] . DIRECTORY_SEPARATOR
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'build'.DIRECTORY_SEPARATOR;

                $extension = $resultCode['extension'];
                $zipName = $resultCode['zipName'] ?? $extension . '.zip';
                $base64Zip = $resultCode['base64Zip'];
                $base64Zip = str_replace('data:image/zip;base64,', '', $base64Zip);
                $base64Zip = str_replace(' ', '+', $base64Zip);

                file_put_contents($importPath.$zipName, base64_decode($base64Zip));

                $unzipFile = $importPath.$zipName;

                GeneralUtility::mkdir_deep($buildPath);
                Tools\ZipArchive::unzip($unzipFile, $buildPath);

                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    'Extension: ' . $this->extensionName,
                    'Extension is build (remote).',
                    ContextualFeedbackSeverity::OK,
                );
                $notificationQueue->enqueue($flashMessage);

                return true;

                break;
			default:
                return false;
		}
	}

    private function buildRequest(): array
    {

// ToDo Make path configurable
        $extensionDevelopmentSourcePath =
            Environment::getPublicPath() . DIRECTORY_SEPARATOR
            . 'fileadmin' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR;

        $exportPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR . $this->configuration['varEb']
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR;

        // Clean up
        GeneralUtility::rmdir($exportPath, true);

        GeneralUtility::mkdir_deep($exportPath . 'source');
        GeneralUtility::mkdir_deep($exportPath . 'debug');
        GeneralUtility::mkdir_deep($exportPath . 'import');
        GeneralUtility::mkdir_deep($exportPath . 'import');

        $extensionSourceExportPath = $exportPath . 'source' . DIRECTORY_SEPARATOR;

		Tools\Folder::copy($extensionDevelopmentSourcePath, $extensionSourceExportPath);

        // Copyback ToDo 
        $extConf = Tools\ExtensionConfiguration::read($extensionDevelopmentSourcePath);
        if ($extConf['extensionBuild']['copyBack'] ?? false) {
            $extensionName = $extConf['extension']['extensionName'];
            $extPath =
                Environment::getPublicPath() . DIRECTORY_SEPARATOR
                . 'typo3conf' . DIRECTORY_SEPARATOR
                . 'ext' . DIRECTORY_SEPARATOR
                . $this->extensionName . DIRECTORY_SEPARATOR;
            $developerCodePath = $extensionDevelopmentSourcePath . 'DeveloperCode' . DIRECTORY_SEPARATOR;
            foreach ($extConf['extensionBuild']['copyBack'] ?? [] as $copyBackName => $copyBackData) {
                if ($copyBackData) {
                    $copyBackSorce = $extPath . $copyBackName;
                    $copyBackDestination = $developerCodePath . $copyBackName;
                    if (is_dir($copyBackSorce)) {
                        Tools\Folder::copy($copyBackSorce, $copyBackDestination);
    				} elseif (is_file($copyBackSorce)) {
                        $path_parts = pathinfo($copyBackDestination);
                        GeneralUtility::mkdir_deep($path_parts['dirname']);
                        copy($copyBackSorce, $copyBackDestination);
    				}
				}
		    }
        }

		$extensionSourcePath = $exportPath . 'source' . DIRECTORY_SEPARATOR;
		$extensionDebugPath = $exportPath . 'debug' . DIRECTORY_SEPARATOR;

        $version = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');

        $multipart = [];
		$multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'build'];
        $multipart['multipart'][] = ['name' => 'version', 'contents' => $version ?? '0.0.0'];

        $multipart['multipart'][] = ['name' => 'developerId', 'contents' => $this->configuration['systemId']];
        $multipart['multipart'][] = ['name' => 'systemId', 'contents' => $this->developer['developerId']];

        $multipart['multipart'][] = ['name' => 'serverIp', 'contents' => 'community'];
        $multipart['multipart'][] = ['name' => 'serverMac', 'contents' => 'community'];

        $multipart['multipart'][] = ['name' => 'vendorHash', 'contents' => '']; // ToDo Check for useing

        $multipart['multipart'][] = ['name' => 'vendor', 'contents' => $this->vendorName];
        $multipart['multipart'][] = ['name' => 'extension',  'contents' => $this->extensionName];

//        $dependentExtensionsList['dependenciesExport'] = $this->foreignExtensionsList; // Todo Docu / fuction check
//        Tools\Json::write(
//            $extensionSourcePath . 'extension.dependencies.export.json',
//            $dependentExtensionsList
//        );
	
		$jsonFileList = GeneralUtility::getFilesInDir($extensionSourcePath, 'json');

        // Build multipart value for JSON-Files
        foreach ($jsonFileList ?? [] as $jsonFile) {
			$base64 = 'data:text/plain;base64,' . base64_encode(file_get_contents($extensionSourcePath . $jsonFile));
            $multipart['multipart'][] = ['name' => $jsonFile, 'contents' => $base64];
        }


        // Build multipart value for DeveloperCode-Flies
        $extensionDevCodePath = $extensionSourcePath . 'DeveloperCode';
		if (file_exists($extensionDevCodePath)) {

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extensionDevCodePath),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($files as $fileName => $fileData) {
                $fileName = str_replace('\\', '/', $fileName);
                $pos = strpos ($fileName, 'DeveloperCode');
				if ($pos > 0) {
                    if (is_file($fileName)) {
                     $fileNameNew = substr ($fileName, $pos);
                     $base64 = 'data:text/plain;base64,' . base64_encode(file_get_contents($fileName));
                     $multipart['multipart'][] = ['name' => $fileNameNew, 'contents' => $base64];
    				}
				}
	    	}
		}

        file_put_contents(
            $extensionDebugPath . 'request.json',
            json_encode($multipart, JSON_PRETTY_PRINT),
        );

        return $multipart;
	}

}