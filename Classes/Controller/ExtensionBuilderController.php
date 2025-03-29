<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Core\Environment;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon; // Removed in TYPO3 v14
use TYPO3\CMS\Core\Imaging\IconSize;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;



use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;

use ExtensionBuilder\ExtensionbuilderTypo3\Extensions;


use ExtensionBuilder\ExtensionbuilderTypo3\Service\ExtensionBuilderService;

class ExtensionBuilderController extends ActionController
{

     /*
      * Abstract class for backend modules.
      * Functions that provide reading and saving of configurations from the backend.
      * ToDo Doc: Helper functs für Backend
      *
      */

    public array $coreStatus = [];
    public array $keyStatus = [];
    public bool $isProKey = false;

    public array $todo = [];
    public array $changeLog = [];
 
    public const DROPDOWN_D = [
        'Developer' => 'edit',
        'Configuration' => 'edit',
        'Info' => 'show'
    ];
    public const DROPDOWN_V = [
        'Vendor' => 'list',
        'Developer' => 'edit',
        'Configuration' => 'edit',
        'Info' => 'show'
    ];
    public const DROPDOWN_E = [
        'Extension' => 'list',
        'Project' => 'list',
//        'NotesAndIdeas' => 'list',
//'Food for thought' => 'list',
        'Vendor' => 'list',
        'Developer' => 'edit',
        'Configuration' => 'edit',
        'Info' => 'show'
    ];
	
	public ModuleTemplate $moduleTemplate;

// ToDo
// customer projects ?

    function __construct(
        protected LanguageServiceFactory $languageServiceFactory,
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected IconFactory $iconFactory,
        protected ExtensionBuilderService $ebService,
    ) {
        $this->coreStatus = Tools\RestApiClient::getStatus(
            $this->ebService->configuration['builderUrl'],
            $this->ebService->configuration['builderApi'],
	    );

        $this->keyStatus = Tools\RestApiClient::checkKey(
//            $this->configuration['builderUrl'],
'https://development.extension-builder.dev',
//            $this->configuration['builderApi'],
'/api/v1/extensionbuilderKey',
            $this->ebService->configuration['systemId'] ?? '',
            $this->ebService->configuration['proVersionKey'] ?? '',
            $this->ebService->developer['developerId'] ?? '',
            $this->ebService->developer['proVersionKey'] ?? '',
		);

// ToDo
        $this->isProKey = $this->keyStatus['proKeyActive'] ?? false;
        $this->ebService->configuration['proKey'] = $this->isProKey;

    }



    final function readComponent(
        string $vendorName,
        string $extensionName,
		string $componentName,
    ): array {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . lcfirst($componentName) . '.json';

       $array = [];

        if (file_exists($fileName)) {
            $array = Tools\Json::read($fileName);
        }
      
        return [];
	}

    final function writeComponent(
        string $vendorName,
        string $extensionName,
		string $componentName,
        array $componentData,
    ): void {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . lcfirst($componentName) . '.json';

        $component = [];
        $component[lcfirst($componentName)] = $componentData;

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        Tools\Json::write($fileName, $component);
	}

    final function renameComponent(
        string $vendorName,
        string $extensionName,
		string $componentName,
		string $componentNewName,
        array $componentData,
    ): void {

	}

    final function deleteComponent(
        string $vendorName,
        string $extensionName,
		string $componentName,
    ): void {

	}



    // Translated

    final function getTranslatedLabel(
        ServerRequestInterface $request,
        string $key,
    ): string {
        $languageService = $this->languageServiceFactory->createFromSiteLanguage(
            $request->getAttribute('language') ?? $request->getAttribute('site')->getDefaultLanguage()
        );

        return $languageService->sL($key);
    }

    // Doc Header Component

    final function addDocHeaderModuleDropDown(
        string $activeEntry,
        string $activeProjcet = '',
        string $activeVendor = '',
    ): void {
        $languageService = $GLOBALS['LANG'];

        if ($this->ebService->noDeveloper) {
            $dropdown = self::DROPDOWN_D;
        } else {
            if ($this->ebService->noVendors) {
                $dropdown = self::DROPDOWN_V;
            } else {
                $dropdown = self::DROPDOWN_E;
			}
        }

        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('ExtensionbuilderJumpMenu');

        foreach ($dropdown as $dropdownName => $dropdownAction) {

            $item = $menu->makeMenuItem()
                ->setTitle($languageService->sL(
                    'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.' . lcfirst($dropdownName))
                )
                ->setHref($this->uriBuilder->uriFor($dropdownAction, [], $dropdownName));
            if ($dropdownName === $activeEntry) {
                $item->setActive(true);
            }
            $menu->addMenuItem($item);
		}
		
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        if ($this->ebService->noDeveloper AND $this->noVendors) { return; }

        if ($activeProjcet and ($this->ebService->projects ?? false)) {
            $menuProjects = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menuProjects->setIdentifier('ExtensionbuilderJumpProjects');

            $item = $menu->makeMenuItem()
                ->setHref($this->uriBuilder->uriFor(
                    'list',
                    ['currentProject' => 'no'],
                    'Extension'
                 ))
                ->setTitle('No project'); // ToDo LLL
            $menuProjects->addMenuItem($item);

            foreach ($this->ebService->projects as $projectKey => $projectData) {
                $item = $menu->makeMenuItem()
                    ->setHref($this->uriBuilder->uriFor(
                        'list',
                        ['currentProject' => $projectKey],
                        'Extension'
                    ))
                    ->setTitle($projectData['name']);

                if ($projectKey === $activeProjcet) {
                    $item->setActive(true);
                }

                $menuProjects->addMenuItem($item);
		    }

            $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menuProjects);
        }

        if (($activeVendor) and (count($this->ebService->vendors ?? []) > 1)) {
            $menuVendors = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
            $menuVendors->setIdentifier('ExtensionbuilderJumpVendors');

            $item = $menu->makeMenuItem()
                ->setHref($this->uriBuilder->uriFor(
                    'list',
                    ['currentVendor' => 'all', 'currentProject' => $activeProjcet],
                    'Extension'
                ))
                ->setTitle('Show all vendors');

            if ($activeVendor === 'all') {
                $item->setActive(true);
            }

            $menuVendors->addMenuItem($item);

            $item = $menu->makeMenuItem()
                ->setHref($this->uriBuilder->uriFor(
                    'list',
                    ['currentVendor' => 'no', 'currentProject' => $activeProjcet],
                    'Extension'
                ))
                ->setTitle('Show no vendors');
            if ($activeVendor === 'no') {
                $item->setActive(true);
            }
            $menuVendors->addMenuItem($item);

            foreach ($this->ebService->vendors as $vendorKey => $vendorData) {
                $item = $menu->makeMenuItem()
                    ->setHref($this->uriBuilder->uriFor(
                        'list',
                        ['currentVendor' => $vendorKey, 'currentProject' => $activeProjcet],
                        'Extension'
                    ))
                    ->setTitle($vendorData['vendorName']);

                if ($vendorKey === $activeVendor) {
                    $item->setActive(true);
                }

                $menuVendors->addMenuItem($item);
	    	}

            $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menuVendors);
		}
    }

    final function addDocHeaderCloseButtons(
        string $action,
        string $controller,
        string $vendorName = '',
        string $extensionName = '',
        string $projectKey = '',
    ): void {
        $languageService = $GLOBALS['LANG'];
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $parameters = [];
        if ($vendorName) { $parameters['vendorName'] =  $vendorName; }
		if ($extensionName) { $parameters['extensionName'] =  $extensionName; }
        if ($projectKey) { $parameters['projectKey'] =  $projectKey; }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $this->iconFactory->getIcon('actions-close', Icon::SIZE_SMALL);
        } else {
            $icon = $this->iconFactory->getIcon('actions-close', IconSize::SMALL);
        }

        $closeButton = $buttonBar->makeLinkButton();
        $closeButton
            ->setTitle(
                $languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close'),
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref($this->uriBuilder->uriFor($action,$parameters,$controller));
        $buttonBar->addButton($closeButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
    }

    final function addDocHeaderCloseAndSaveButtons(
        string $closeAction,
        string $closeController,
        string $saveFromId,
        string $vendorName = '',
        string $extensionName = '',
        string $projectKey = '',
    ): void {
        $languageService = $GLOBALS['LANG'];
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $parameters = [];
        if ($vendorName) { $parameters['vendorName'] =  $vendorName; }
		if ($extensionName) { $parameters['extensionName'] =  $extensionName; }
        if ($projectKey) { $parameters['projectKey'] =  $projectKey; }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $this->iconFactory->getIcon('actions-close', Icon::SIZE_SMALL);
        } else {
            $icon = $this->iconFactory->getIcon('actions-close', IconSize::SMALL);
        }
        $closeButton = $buttonBar->makeLinkButton();
        $closeButton
            ->setTitle($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:close'))
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref($this->uriBuilder->uriFor($closeAction,$parameters,$closeController));
        $buttonBar->addButton($closeButton);


        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $this->iconFactory->getIcon('actions-save', Icon::SIZE_SMALL);
        } else {
            $icon = $this->iconFactory->getIcon('actions-save', IconSize::SMALL);
        }

        $saveButton = $buttonBar->makeInputButton()
            ->setTitle($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:save'))
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setName('cmd')
            ->setValue('save')
            ->setForm($saveFromId);

        $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
    }

    final function addDocHeaderAddButton(
        string $addAction,
        string $addController,
        array $addParameters = [],
    ): void {
        $languageService = $GLOBALS['LANG'];
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $this->iconFactory->getIcon('actions-archive', Icon::SIZE_SMALL);
        } else {
            $icon = $this->iconFactory->getIcon('actions-archive', IconSize::SMALL);
        }

        $lll = 
            'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.'
             . strtolower($addController)
             . '.xlf:link.'
			 . strtolower($addAction);

        $linkButton = $buttonBar->makeLinkButton()
            ->setTitle(
                $languageService->sL($lll)
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref($this->uriBuilder->uriFor($addAction, $addParameters, $addController));

        $buttonBar->addButton($linkButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
    }


    final function addDocHeaderImportExampleVendor(
        string $importAction,
        string $importController,
    ): void {
        $languageService = $GLOBALS['LANG'];
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() == 12 ) {
            $icon = $this->iconFactory->getIcon('actions-archive', Icon::SIZE_SMALL);
        } else {
            $icon = $this->iconFactory->getIcon('actions-archive', IconSize::SMALL);
        }

        $addButton = $buttonBar->makeLinkButton()
            ->setTitle($languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:importExample'))
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref($this->uriBuilder->uriFor($importAction, [], $importController));
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