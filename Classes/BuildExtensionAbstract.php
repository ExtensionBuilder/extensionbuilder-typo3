<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Core\Core\Environment;

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;

// FlashMessage
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;

abstract class  BuildExtensionAbstract
{

    public bool $isComposerMode = false;

    public array $configuration = [];
    public array $developer = [];
    public array $projects = [];
    public array $vendors = [];
    public array $vendorsAndExtensions = [];
    public array $localExtensions = [];
    public array $foreignExtensions = [];

    public bool $noDeveloper = true;
    public bool $noVendors = true;
 
    public const DROPDOWN_D = ['developer', 'configuration', 'getstarted', 'info',];
    public const DROPDOWN_V = ['vendor', 'developer', 'configuration', 'getstarted', 'info',];
    public const DROPDOWN_E = ['extension', 'project', 'vendor', 'developer', 'configuration', 'getstarted', 'info',];

    /**
     *
     */
    function __construct(
    ) {
        $this->isComposerMode = Environment::isComposerMode();
        $this->readConfiguration();
        $this->readDeveloper();
        $this->readVendors();
        $this->readVendorsAndExtensions();
        $this->readProjects();
        $this->getLocalExtensions();
        $this->getForeignExtensions();
    }


    /**
     *
     */
    final function readConfiguration(): void
    {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder().
            'TYPO3'.DIRECTORY_SEPARATOR.'configuration.json';
        if (file_exists($fileName)) {
            $configurationJson = Tools\Json::read($fileName);
            $this->configuration = $configurationJson['configuration'] ?? [];
        } else {
            $this->configuration = [];
            $this->configuration['proVersion'] = false;
            $this->configuration['proVersionKey'] = '';
            $this->configuration['builderUrl'] = 'typo3.extension-builder.dev';
		}
	}


    /**
     *
     */
    final function readDeveloper(): void
    {
        $fileName = 
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . $GLOBALS['BE_USER']->user['username'] . '.json';
        if (file_exists($fileName)) {
            $developerJson = Tools\Json::read($fileName);
            $this->developer = $developerJson['developer'] ?? [];
            $this->noDeveloper = false;
        } else {
            $this->developer = [];
            $this->developer['author'] = $GLOBALS['BE_USER']->user['realName'] ?? '';
            $this->developer['author_email'] = $GLOBALS['BE_USER']->user['email'] ?? '';
            $this->developer['author_company'] = $GLOBALS['BE_USER']->user['company'] ?? '';
		}

	}


    /**
     *
     */
    final function readProjects(): void
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
	}


    /**
     *
     */
    final function writeProjects(): void
    {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . 'projects.json';
        $projects = [];
        $projects['projects'] = $this->projects;
        Tools\Json::write($fileName, $projects);
	}


    /**
     *
     */
    final function readVendors(): void
    {
        $path = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();

        $vendorList = Tools\Folder::scanFolderForDirectory($path);
        foreach($vendorList ?? [] as $vendorName) {
            $this->noVendors = false;
            $fileName =
                Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
                . 'TYPO3' . DIRECTORY_SEPARATOR
                . $vendorName.DIRECTORY_SEPARATOR
                . 'vendor.json';
            if (file_exists($fileName)) {
                $vendor = Tools\Json::read($fileName);
                $this->vendors[$vendorName] = $vendor['vendor'] ?? [];
            }
		}

	}




    /**
     *
     */
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

            // Read Vendordata
            $vendorJsonList = Tools\Folder::scanFolderForFile($extensionsFolder . DIRECTORY_SEPARATOR . $vendor, 'json');
            foreach ($vendorJsonList ?? [] as $json) {

                //
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

                        // Extension Liste erstellen
                        $extensionList = Tools\Folder::scanFolderForDirectory($extensionsFolder . DIRECTORY_SEPARATOR . $vendor);
                        foreach ($extensionList ?? [] as $extension) {

                            // Extesion einlesen
                            if (!($vendorsAndExtensions[$vendor]['extensions'] ?? false)) {
                                $vendorsAndExtensions[$vendor]['extensions'] = [];
                            }

                            $vendorsAndExtensions[$vendor]['extensions'][$extension] = [];

                            $extensionPath =
                                $extensionsFolder.DIRECTORY_SEPARATOR
                                . $vendor.DIRECTORY_SEPARATOR
                                . $extension.DIRECTORY_SEPARATOR;

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


    /**
     * Initializes the plugin.
     *
     *
     * ToDo
     *  - Composer
     *  - Check stauts active / deactive
     */
    final function getLocalExtensions(): void
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


    /**
     *
     */
    final function getForeignExtensions(): void
	{
        // Duchsucht die Extenions nach eb_ext_export.json und liest disen ein und gibt eine Array zurück.

        // ToDo nur depencs ext laden

        $returnArray = [];

        $extensionsPath =
            \TYPO3\CMS\Core\Core\Environment::getProjectPath().DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;

        foreach ($this->localExtensionList ?? [] as $extensionsName => $extensions) {
            $tmpFile = $extensionsPath . DIRECTORY_SEPARATOR . $extensionsName . DIRECTORY_SEPARATOR . 'eb_ext_export.json';
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



    // Helper

    /**
     *
     */
    final function addDocHeaderModuleDropDown(
        ModuleTemplate $moduleTemplate,
        UriBuilder $uriBuilder,
        string $activeEntry = '',
        string $activeProjcet = 'no',
        string $activevendor = 'all',
    ): void {

//debug($this, 'BuildExtensionAbstract.php - 309');

        $languageService = $GLOBALS['LANG'];
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('ExtensionbuilderJumpMenu');

        if ($this->noDeveloper) {
            $dropdown = self::DROPDOWN_D;
        } else {
            if ($this->noVendors) {
                $dropdown = self::DROPDOWN_V;
            } else {
                $dropdown = self::DROPDOWN_E;
			}
        }

        foreach ($dropdown as $entry) {
            $item = $menu->makeMenuItem()
                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $entry))
                ->setTitle($languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.' . $entry));
            if ($entry === $activeEntry) {
                $item->setActive(true);
            }
            $menu->addMenuItem($item);
		}
		
        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        if ($this->noDeveloper AND $this->noVendors) {
            return;
		}


        $menuProjects = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menuProjects->setIdentifier('ExtensionbuilderJumpProjects');

        foreach ($this->projects as $projectData) {
            $item = $menu->makeMenuItem()
                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $entry))
                ->setTitle($projectData['projectName']);
//            if ($entry === $activeEntry) {
//                $item->setActive(true);
//            }
            $menuProjects->addMenuItem($item);
		}

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menuProjects);



        if ($this->vendors) {

            $menuVendors = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menuVendors->setIdentifier('ExtensionbuilderJumpVendors');

            $item = $menu->makeMenuItem()
//                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $vendor))
                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.vendor'))
                ->setTitle('all');

            $menuVendors->addMenuItem($item);

        foreach ($this->vendors as $vendor) {

            $item = $menu->makeMenuItem()
//                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $vendor))
                ->setHref((string) $uriBuilder->buildUriFromRoute('extensionbuilder_typo3.vendor'))
                ->setTitle($vendor['vendorName']);

//            if ($entry === $activeEntry) {
//                $item->setActive(true);
//            }
            $menuVendors->addMenuItem($item);
		}

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menuVendors);

		}
    }


    /**
     *
     */
    final function addDocHeaderCloseButtons(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
        string $uriRoute,
    ): void {
        $languageService = $GLOBALS['LANG'];
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $closeButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close')
            )
            ->setShowLabelText(true)
            ->setIcon($iconFactory->getIcon('actions-close', Icon::SIZE_SMALL))
            ->setHref((string)$uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $uriRoute));
        $buttonBar->addButton($closeButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
    }


    /**
     *
     */
    final function addDocHeaderCloseAndSaveButtons(
        ModuleTemplate $moduleTemplate,
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
        string $uriRoute,
        string $vendorName = '',
        string $extensionName = '',
    ): void {
        $languageService = $GLOBALS['LANG'];
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        if ($vendorName && $extensionName) {
            $closeButton = $buttonBar->makeLinkButton()
                ->setTitle(
                    $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close')
                )
                ->setShowLabelText(true)
                ->setIcon($iconFactory->getIcon('actions-close', Icon::SIZE_SMALL))
                ->setHref((string)$uriBuilder->buildUriFromRoute(
                    'extensionbuilder_typo3.'.$uriRoute,
                    ['vendorName' => $vendorName, 'extensionName' => $extensionName ]
                ));
        } else {
            $closeButton = $buttonBar->makeLinkButton()
                ->setTitle(
                    $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close')
                )
                ->setShowLabelText(true)
                ->setIcon($iconFactory->getIcon('actions-close', Icon::SIZE_SMALL))
                ->setHref((string)$uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $uriRoute));
        }
        $buttonBar->addButton($closeButton, ButtonBar::BUTTON_POSITION_LEFT, 2);

        $saveButton = $buttonBar->makeInputButton()
            ->setName('CMD')
            ->setValue('save')
            ->setForm('tx_extensionbuilder_typo3_form')
            ->setIcon($iconFactory->getIcon('actions-document-save', Icon::SIZE_SMALL))
            ->setTitle(
                $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:save')
            )
            ->setShowLabelText(true);
        $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 3);

    }


    /**
     *
     */
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
        $addButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL(
                    'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/' . $uriRouteTextLLL
                )
            )
            ->setShowLabelText(true)
            ->setIcon(
                $iconFactory->getIcon('actions-add', Icon::SIZE_SMALL)
            )
            ->setHref(
                (string)$uriBuilder->buildUriFromRoute('extensionbuilder_typo3.' . $uriRoute, $uriRouteParameters)
            );

        $buttonBar->addButton($addButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
    }


    /**
     *
     */
    final function flashMessage(
        string $flashMessage1,
        string $flashMessage2,
        ContextualFeedbackSeverity $feedback = ContextualFeedbackSeverity::OK,
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