<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Core\Environment;

use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon; // Removed in TYPO3 v14
use TYPO3\CMS\Core\Imaging\IconSize;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

abstract class BuildExtensionAbstract
{

    public bool $isComposerMode = false;
    public bool $noDeveloper = true;
    public bool $noVendors = true;

    public array $configuration = [];
    public array $developer = [];
    public array $projects = [];
    public array $vendors = [];
    public array $vendorsAndExtensions = [];
    public array $localExtensions = [];
    public array $foreignExtensions = [];

    public array $todo = [];
    public array $changeLog = [];
 
    public const DROPDOWN_D = ['developer', 'configuration', 'info',];
    public const DROPDOWN_V = ['vendor', 'developer', 'configuration', 'info',];
    public const DROPDOWN_E = ['extension', 'project', 'vendor', 'developer', 'configuration', 'info',];

    function __construct(
    ) {
        $this->isComposerMode = Environment::isComposerMode();
        $this->readConfiguration();
        $this->readDeveloper();
        $this->readVendor();
        $this->readVendorsAndExtensions();
        $this->readProject();
        $this->getLocalExtension();
        $this->getForeignExtension();
    }

    final function readConfiguration(): void
    {
        $this->configuration = [];

        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'configuration.json';

        if (file_exists($fileName)) {
            $configurationJson = Tools\Json::read($fileName);
            $this->configuration = $configurationJson['configuration'] ?? [];
            Tools\ConfigArray::arrayMerge($this->configuration, $this->configuration['typo3']);
            unset($this->configuration['typo3']);
        } else {
            $this->configuration['systemId'] = Tools\Uuid::uuid();
            $this->configuration['proVersion'] = false;
            $this->configuration['proVersionKey'] = '';
            $this->configuration['builderUrl'] = 'https://typo3.extension-builder.dev/';

//    public const EXT_NAME = 'extensionbuilder_typo3';
//    public const VAR_EB = 'ExtensionBuilder' . DIRECTORY_SEPARATOR . 'TYPO3' . DIRECTORY_SEPARATOR;
//    public const VAR_EB_CORE = 'ExtensionBuilderCore' . DIRECTORY_SEPARATOR . 'TYPO3' . DIRECTORY_SEPARATOR;
//    public const REMOTE_API = 'extensionbuildcoretypo3'

            $this->writeConfiguration();
        }

        /* DevelopmentCodeStart */
        $this->configuration['developmentServer'] = false;
        if ($_SERVER['SERVER_NAME'] === 'development.extension-builder.dev') {
            $this->configuration['developmentServer'] = true;
            $this->configuration['builderUrl'] = 'https://development.extension-builder.dev/';
        } else {
            $this->configuration['developmentServer'] = false;
		}
        /* DevelopmentCodeEnd */

	}

    final function writeConfiguration(): void
    {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'configuration.json';

        $configuration = [];
        $configuration['configuration'] = [];
        $configuration['configuration']['systemId'] = $this->configuration['systemId'];
        $configuration['configuration']['typo3'] = $this->configuration;
        unset($configuration['configuration']['typo3']['systemId']);

        Tools\Json::write($fileName, $configuration);
	}

    final function readDeveloper(): void
    {
        $fileName = 
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'developer.' . $GLOBALS['BE_USER']->user['username'] . '.json';

        if (file_exists($fileName)) {
            $developerJson = Tools\Json::read($fileName);
            $this->developer = $developerJson['developer'] ?? [];
            $this->noDeveloper = false;
		} else {
            $this->noDeveloper = true;			
            $this->developer = [];
            $this->developer['author'] = $GLOBALS['BE_USER']->user['realName'] ?? '';
            $this->developer['author_email'] = $GLOBALS['BE_USER']->user['email'] ?? '';
            $this->developer['author_company'] = $GLOBALS['BE_USER']->user['company'] ?? '';
		}

        if (!($this->developer['developerId'] ?? false)) {
            $this->developer['developerId'] = Tools\Uuid::uuid();
		}
	}

    final function writeDeveloper(): void
    {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'developer.' . $GLOBALS['BE_USER']->user['username'] . '.json';

        $developer = [];
        $developer['developer'] = $this->developer;

        $this->noDeveloper = false;

        Tools\Json::write($fileName, $developer);
	}

    final function readVendor(): void
    {
        $path = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();

        $vendorList = Tools\Folder::scanFolderForDirectory($path);

        foreach($vendorList ?? [] as $vendorName) {
            $this->noVendors = false;
            $fileName =
                Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
                . 'TYPO3' . DIRECTORY_SEPARATOR
                . $vendorName . DIRECTORY_SEPARATOR
                . 'vendor.json';

            if (file_exists($fileName)) {
                $vendor = Tools\Json::read($fileName);
                $this->vendors[$vendorName] = [];
                $this->vendors[$vendorName] = $vendor['vendor'] ?? [];
            }
		}
	}

    final function writeVendor(
        string $vendorName,
    ): void {
        $path = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();

        $filePath =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $vendorName . DIRECTORY_SEPARATOR;

        $fileName = $filePath . 'vendor.json';

        if (!is_dir($filePath)) { GeneralUtility::mkdir_deep($filePath); }

        $vendorData = $this->vendors[$vendorName];

        // Trim please
	    foreach ($vendorData ?? [] as $vendorField) {
	        if (is_string($vendorField)) {
	            $vendorField = trim($vendorField);
	        }
	    }
		
        $vendor = [];
        $vendor['vendor'] = $vendorData;

        $this->noVendors = false;

        Tools\Json::write($fileName, $vendor);
	}

    final function renameVendor(
        string $vendorNameOld,
        string $vendorNameNew,
    ): void {
// ToDo
	}

    final function deleteVendor(
        string $vendorName,
    ): void {
        $filePath =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $vendorName . DIRECTORY_SEPARATOR;

        unset($this->vendors[$vendorName]);
        GeneralUtility::rmdir($filePath, true);
	}

    final function readProject(): void
    {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . 'projects.json';

        if (file_exists($fileName)) {
            $projects = Tools\Json::read($fileName);
            $this->projects = $projects['projects'] ?? [];
		} else {
            $this->projects = [];
		}

        foreach($this->projects ?? [] as $projectKey => $projectData) {
            $extensions = [];
            $dependencies = [];

            foreach(($projectData['extensions'] ?? []) as $extensionKey => $extensionData) {
                foreach($this->vendorsAndExtensions ?? [] as $vendorsKey => $vendorsData) {
                    foreach($vendorsData['extensions'] ?? [] as $vendorExtensionKey => $vendorExtensionData) {
                        if($vendorExtensionKey === $extensionKey) {
                            $extensions[$vendorExtensionKey] = $vendorExtensionData;
                            $extensions[$vendorExtensionKey]['extensionOnOff'] = $extensionData;
                            if ($vendorExtensionData['extension']['depends'] ?? false) {
                                foreach(($vendorExtensionData['extension']['depends'] ?? []) as $dependKey => $dependData) {
                                    foreach($this->vendorsAndExtensions ?? [] as $vendorsKey => $vendorsData) {
                                        foreach($vendorsData['extensions'] ?? [] as $vendorExtensionKey => $vendorExtensionData) {
                                            if($vendorExtensionKey === $dependKey) {
                                                $dependencies[$vendorExtensionKey] = $vendorExtensionData;
                                            }
                                        }
                                    }
			                    }
                            }
                        }
				    }
			    }
			}

			$this->projects[$projectKey]['extensions'] = $extensions;
			$this->projects[$projectKey]['dependencies'] = $dependencies;
        }
	}

    final function writeProject(): void
    {
        $projects = $this->projects;

        foreach($projects ?? [] as $projectKey => $projectData) {
            foreach($projectData['extensions'] ?? [] as $extensionKey => $extensionData) {
				$extensionOnOff = $projects[$projectKey]['extensions'][$extensionKey]['extensionOnOff'];
                unset ($projects[$projectKey]['extensions'][$extensionKey]);
                $projects[$projectKey]['extensions'][$extensionKey] = $extensionOnOff;
            }
            if (!($projects[$projectKey]['extensions'] ?? false)) { $projects[$projectKey]['extensions'] = []; }
            unset ($projects[$projectKey]['dependencies']);
        }

        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . 'projects.json';

        $projectsNew = [];
        $projectsNew['projects'] = $projects;

        if (!$projectsNew['projects'] ) {
            unlink($fileName);
        } else {
            Tools\Json::write($fileName, $projectsNew);
		}
	}

    final function duplicateProjects(
        string $projectKey,
        string $projectKeyNew,
        string $projectName,
    ): void {
        $this->projects[$projectKeyNew] = $this->projects[$projectKey];
        $this->projects[$projectKey]['name'] = $projectName;

        $this->writeProject();
	}

    final function renameProject(
        string $projectKey,
        string $projectName,
    ): void {
// ToDo
        $this->projects[$projectKey]['name'] = $projectName;

        $this->writeProject();
	}

    final function deleteProjects(
        string $projectName
    ): void {
// ToDo
	}

    final function readVendorsAndExtensions(): void
    {
//
// ToDo
//  - Clean Up
//
        $extensionsFolder = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();

        $vendorsAndExtensions = [];

        // Get Vendors from directory
        $vendorList = Tools\Folder::scanFolderForDirectory($extensionsFolder);
        foreach ($vendorList ?? [] as $vendor) {

            // Read Vendor data
            $vendorJsonList = Tools\Folder::scanFolderForFile($extensionsFolder . DIRECTORY_SEPARATOR . $vendor, 'json');
            foreach ($vendorJsonList ?? [] as $json) {

                $jsonData = Tools\Json::read($extensionsFolder . DIRECTORY_SEPARATOR . $vendor . DIRECTORY_SEPARATOR . $json);
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

                        if (!($vendorsAndExtensions[$vendor] ?? false)) {
                            $vendorsAndExtensions[$vendor] = [];
                        }
                        $vendorsAndExtensions[$vendor] = $jsonData['vendor'];

                        // Create extension list
                        $extensionList = Tools\Folder::scanFolderForDirectory($extensionsFolder . DIRECTORY_SEPARATOR . $vendor);
                        foreach ($extensionList ?? [] as $extension) {

                            // Read extension configuration
                            if (!($vendorsAndExtensions[$vendor]['extensions'] ?? false)) {
                                $vendorsAndExtensions[$vendor]['extensions'] = [];
                            }

                            $vendorsAndExtensions[$vendor]['extensions'][$extension] = [];

                            $extensionPath =
                                $extensionsFolder . DIRECTORY_SEPARATOR
                                . $vendor . DIRECTORY_SEPARATOR
                                . $extension . DIRECTORY_SEPARATOR;

                            $vendorsAndExtensions[$vendor]['extensions'][$extension] =
                                Tools\ExtensionConfiguration::read($extensionPath);
                        }

                    }
				} else {
                    if(!($vendorsAndExtensions[$vendor] ?? false)) {
                        $vendorsAndExtensions[$vendor] = [];
                    }
                    $vendorsAndExtensions[$vendor]['jsonErrorInFile'][$json] = $jsonData['JsonError'];;
				}
            }	
        }  

        $this->vendorsAndExtensions = $vendorsAndExtensions;
	}

    final function getRegisteredVendorGroups(): array
    {
        $array = [];
        foreach ($this->extensionbuilderObject->vendorsAndExtensions ?? [] as $vendor) {
            $array[] = $vendor['vendorName'];
        }
        return $array;
    }

    final function getLocalExtension(): void
	{
        $returnArray = [];

        if ($this->isComposerMode){

		} else {
            $extensionsPath =
                \TYPO3\CMS\Core\Core\Environment::getProjectPath() . DIRECTORY_SEPARATOR
                . 'typo3conf' . DIRECTORY_SEPARATOR
                . 'ext' . DIRECTORY_SEPARATOR;
            $extensionsList = Tools\Folder::scanFolderForDirectory($extensionsPath);
            foreach ($extensionsList ?? [] as $extensionsName) {
                $returnArray[$extensionsName] = [];
                $returnArray[$extensionsName]['active'] = false;
            }
		}
        $this->localExtensions = $returnArray;
	}

    final function getForeignExtension(): void
	{
        // Duchsucht die Extenions nach eb_ext_export.json und liest disen ein und gibt eine Array zurück.

        // ToDo nur depencs ext laden

        $returnArray = [];

        $extensionsPath =
            \TYPO3\CMS\Core\Core\Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;

        foreach ($this->localExtensionList ?? [] as $extensionName => $extensionData) {
            $tmpFile = $extensionsPath . DIRECTORY_SEPARATOR . $extensionName . DIRECTORY_SEPARATOR . 'eb_ext_export.json';
            if (file_exists($tmpFile)) {
                $jsonData = Tools\Json::read($tmpFile);
                if ($jsonData ?? false) {
                    $jsonData = $jsonData ?? []; // Knoten entfernen
				    Tools\ConfigArray::arrayMerge($returnArray, $jsonData);
                }
	        }
		}

        $this->foreignExtensions = $returnArray;
	}

    final function getTranslatedLabel(
        ServerRequestInterface $request,
        string $key,
    ): string {
        $languageService = $this->languageServiceFactory->createFromSiteLanguage(
            $request->getAttribute('language') ?? $request->getAttribute('site')->getDefaultLanguage()
        );

        return $languageService->sL($key);
    }

    final function addDocHeaderModuleDropDown(
        ModuleTemplate $moduleTemplate,
        UriBuilder $uriBuilder,
        string $activeEntry = '',
        string $activeProjcet = '',
        string $activeVendor = '',
    ): void {
        if ($this->noDeveloper) {
            $dropdown = self::DROPDOWN_D;
        } else {
            if ($this->noVendors) {
                $dropdown = self::DROPDOWN_V;
            } else {
                $dropdown = self::DROPDOWN_E;
			}
        }

        $languageService = $GLOBALS['LANG'];
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('ExtensionbuilderJumpMenu');

        foreach ($dropdown as $entry) {
            $item = $menu->makeMenuItem()
                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $entry))
                ->setTitle($languageService->sL(
                    'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.' . $entry)
                );
            if ($entry === $activeEntry) {
                $item->setActive(true);
            }
            $menu->addMenuItem($item);
		}
		
        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        if ($this->noDeveloper AND $this->noVendors) { return; }

        if ($activeProjcet and ($this->projects ?? false)) {
            $menuProjects = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menuProjects->setIdentifier('ExtensionbuilderJumpProjects');

            $item = $menu->makeMenuItem()
                ->setHref((string) $uriBuilder->buildUriFromRoute(
                    'extensionbuilder_typo3.extension',
                    ['currentProject' => 'no'],
                ))
                ->setTitle('No project'); // ToDo LLL
            $menuProjects->addMenuItem($item);

            foreach ($this->projects as $projectKey => $projectData) {
                $item = $menu->makeMenuItem()
                    ->setHref((string) $uriBuilder->buildUriFromRoute(
                        'extensionbuilder_typo3.extension',
                        ['currentProject' => $projectKey],
                    ))
                    ->setTitle($projectData['name']);

                if ($projectKey === $activeProjcet) {
                    $item->setActive(true);
                }

                $menuProjects->addMenuItem($item);
		    }

            $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menuProjects);
        }

        if (($activeVendor) and (count($this->vendors ?? []) > 1)) {
            $menuVendors = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menuVendors->setIdentifier('ExtensionbuilderJumpVendors');

            $item = $menu->makeMenuItem()
                ->setHref((string) $uriBuilder->buildUriFromRoute(
                    'extensionbuilder_typo3.extension',
                    ['currentVendor' => 'all'],
                ))
                ->setTitle('Show all vendors');
            if ($activeVendor === 'all') {
                $item->setActive(true);
            }
            $menuVendors->addMenuItem($item);

            $item = $menu->makeMenuItem()
                ->setHref((string) $uriBuilder->buildUriFromRoute(
                    'extensionbuilder_typo3.extension',
                    ['currentVendor' => 'no'],
                ))
                ->setTitle('Show no vendors');
            if ($activeVendor === 'no') {
                $item->setActive(true);
            }
            $menuVendors->addMenuItem($item);

            foreach ($this->vendors as $vendorKey => $vendorData) {
                $item = $menu->makeMenuItem()
                    ->setHref((string) $uriBuilder->buildUriFromRoute(
                        'extensionbuilder_typo3.extension',
                        ['currentVendor' => $vendorKey],
                    ))
                    ->setTitle($vendorData['vendorName']);
                if ($vendorKey === $activeVendor) {
                    $item->setActive(true);
                }
                $menuVendors->addMenuItem($item);
	    	}

            $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menuVendors);
		}
    }

    final function addDocHeaderCloseButtons(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
        string $uriRoute,
        string $vendorName = '',
        string $extensionName = '',
        string $projectKey = '',
    ): void {
        $languageService = $GLOBALS['LANG'];

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $uriRouteParameters = [];
        if ($vendorName) { $uriRouteParameters['vendorName'] =  $vendorName; }
		if ($extensionName) { $uriRouteParameters['extensionName'] =  $extensionName; }
        if ($projectKey) { $uriRouteParameters['projectKey'] =  $projectKey; }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $iconFactory->getIcon('actions-close', Icon::SIZE_SMALL);
        } else {
            $icon = $iconFactory->getIcon('actions-close', IconSize::SMALL);
        }
        $closeButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close')
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref((string)$uriBuilder->buildUriFromRoute(
                'extensionbuilder_typo3.' . $uriRoute,
                $uriRouteParameters,
            ));
        $buttonBar->addButton($closeButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
    }

    final function addDocHeaderCloseAndSaveButtons(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
        string $uriRoute,
        string $vendorName = '',
        string $extensionName = '',
        string $projectKey = '',
    ): void {
        $languageService = $GLOBALS['LANG'];

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $uriRouteParameters = [];
        if ($vendorName) { $uriRouteParameters['vendorName'] =  $vendorName; }
		if ($extensionName) { $uriRouteParameters['extensionName'] =  $extensionName; }
        if ($projectKey) { $uriRouteParameters['projectKey'] =  $projectKey; }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $iconFactory->getIcon('actions-close', Icon::SIZE_SMALL);
        } else {
            $icon = $iconFactory->getIcon('actions-close', IconSize::SMALL);
        }
        $closeButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close'),
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref((string)$uriBuilder->buildUriFromRoute(
                'extensionbuilder_typo3.' . $uriRoute,
                $uriRouteParameters,
            ));
        $buttonBar->addButton($closeButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $iconFactory->getIcon('actions-save', Icon::SIZE_SMALL);
        } else {
            $icon = $iconFactory->getIcon('actions-save', IconSize::SMALL);
        }
        $saveButton = $buttonBar->makeInputButton()
            ->setName('action')
            ->setValue('save')
            ->setForm('tx_extensionbuilder_form')
            ->setIcon($icon)
            ->setTitle(
                $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:save')
            )
            ->setShowLabelText(true);
        $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
    }

    final function addDocHeaderAddButton(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
		string $uriRouteTextLLL,
        string $uriRoute,
        array $uriRouteParameters = [],
    ): void {
        $languageService = $GLOBALS['LANG'];

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $iconFactory->getIcon('actions-archive', Icon::SIZE_SMALL);
        } else {
            $icon = $iconFactory->getIcon('actions-archive', IconSize::SMALL);
        }
        $addButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/' . $uriRouteTextLLL)
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                (string)$uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $uriRoute, $uriRouteParameters)
            );

        $buttonBar->addButton($addButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
    }

    final function addDocHeaderClearButton(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
		string $uriRouteTextLLL,
        string $uriRoute,
        array $uriRouteParameters = [],
    ): void {
        $languageService = $GLOBALS['LANG'];

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $iconFactory->getIcon('actions-add', Icon::SIZE_SMALL);
        } else {
            $icon = $iconFactory->getIcon('actions-v', IconSize::SMALL);
        }
        $addButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/' . $uriRouteTextLLL)
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                (string)$uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $uriRoute, $uriRouteParameters)
            );

        $buttonBar->addButton($addButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
    }


    final function addDocHeaderImportExampleVendor(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
		string $uriRouteTextLLL,
        string $uriRoute,
        array $uriRouteParameters = [],
    ): void {
        $languageService = $GLOBALS['LANG'];

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $iconFactory->getIcon('actions-archive', Icon::SIZE_SMALL);
        } else {
            $icon = $iconFactory->getIcon('actions-archive', IconSize::SMALL);
        }
        $addButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/' . $uriRouteTextLLL)
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                (string)$uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $uriRoute, $uriRouteParameters)
            );

        $buttonBar->addButton($addButton, ButtonBar::BUTTON_POSITION_LEFT, 4);
    }

    final function flashMessage(
        string $flashMessage1,
        string $flashMessage2,
        ContextualFeedbackSeverity $feedback = ContextualFeedbackSeverity::OK
    ): void {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $flashMessage1,
            $flashMessage2,
            $feedback,
        );
        $notificationQueue->enqueue($flashMessage);
	}

}