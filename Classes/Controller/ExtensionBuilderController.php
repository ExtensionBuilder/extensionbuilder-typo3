<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Service\BackendService;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownRadio;

use TYPO3\CMS\Backend\Template\Components\ComponentFactory;

use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;

// Deprecation: #107823 - ButtonBar, Menu, and MenuRegistry make* methods deprecated 14.0
use TYPO3\CMS\Core\Imaging\IconSize;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use TYPO3\CMS\Core\Utility\GeneralUtility;

//use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

use TYPO3\CMS\Backend\Template\Components\Buttons\GenericButton;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
class ExtensionBuilderController extends ActionController
{
    /** @var array<string, mixed> */
    public array $coreStatus = [];
    /** @var array<string, mixed> */
    public array $keyStatus = [];
    /** @var array<string, mixed> */
    public array $todo = [];
    /** @var array<string, mixed> */
    public array $changeLog = [];
    public bool $isProKey = false;
    public ModuleTemplate $moduleTemplate;

    private ?object $componentFactory = null;

    /**
     * @since 0.12
     */
    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly PageRenderer $pageRenderer,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        protected BackendService $ebBackendService,
    ) {
        // Deprecation: #107823 - ButtonBar, Menu, and MenuRegistry make* methods deprecated 14.0
        if (version_compare(VersionNumberUtility::getNumericTypo3Version(), '14.0.0', '>=')) {
            $this->componentFactory = GeneralUtility::makeInstance(ComponentFactory::class);
        }
    }

    // Translated

    /**
     * @since 0.12
     */
    final public function getTranslatedLabel(
        ServerRequestInterface $request,
        string $key,
    ): string {
        $languageService = $this->languageServiceFactory->createFromSiteLanguage(
            $request->getAttribute('language')
                ?? $request->getAttribute('site')->getDefaultLanguage()
        );

        return $languageService->sL($key);
    }

    /**
     * @since 0.12
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @since 0.12
     */
    protected function getLanguageService(): LanguageService
    {
        return $this->languageServiceFactory
            ->createFromUserPreferences($this->getBackendUser());
    }

    // Doc Header Component

    /**
     * @since 0.12
     */
    final public function addDocHeaderModuleDropDown(
        string $activeEntry,
        string $activeProject = '',
        string $activeVendor = '',
    ): void {
        $languageService = self::getLanguageService();

        $isTypo3V14OrHigher = version_compare(
            VersionNumberUtility::getNumericTypo3Version(),
            '14.0.0',
            '>='
        );

        $dropdown = [
            'Extension' => 'list',
            'Project' => 'list',
            'Vendor' => 'list',
            'Developer' => 'edit',
            'DeveloperHub' => 'show',
            'Configuration' => 'edit',
            'Info' => 'show',
        ];

        if (!($this->ebBackendService->beUserIsAdmin)) {
            unset($dropdown['Configuration']);
        }

        if ($this->ebBackendService->noDeveloper) {
            unset(
                $dropdown['Extension'],
                $dropdown['Project'],
                $dropdown['Vendor']
            );
        } elseif ($this->ebBackendService->noVendors) {
            unset(
                $dropdown['Extension'],
                $dropdown['Project']
            );
        }

        $docHeaderComponent = $this->moduleTemplate->getDocHeaderComponent();
        $menuRegistry = $docHeaderComponent->getMenuRegistry();
        $buttonBar = $docHeaderComponent->getButtonBar();

        // Main module selector stays in MenuRegistry.
        if ($isTypo3V14OrHigher) {
            $menu = $this->componentFactory->createMenu();
        } else {
            // @extensionScannerIgnoreLine
            $menu = $menuRegistry->makeMenu();
        }

        $menu->setIdentifier('ExtensionbuilderJumpMenu');

        foreach ($dropdown as $dropdownName => $dropdownAction) {
            if ($isTypo3V14OrHigher) {
                $item = $this->componentFactory->createMenuItem();
            } else {
                // @extensionScannerIgnoreLine
                $item = $menu->makeMenuItem();
            }

            $item
                ->setTitle(
                    $languageService->sL(
                        $this->ebBackendService->lll
                        . '.xlf:function.'
                        . lcfirst($dropdownName)
                    )
                )
                ->setHref(
                    $this->uriBuilder->uriFor(
                        $dropdownAction,
                        [],
                        $dropdownName
                    )
                );

            if ($dropdownName === $activeEntry) {
                $item->setActive(true);
            }

            $menu->addMenuItem($item);
        }

        $menuRegistry->addMenu($menu);

        if ($activeEntry !== 'Extension') {
            return;
        }

        // Show if projects are present

        $projectCount = 0;

        foreach ($this->ebBackendService->projects as $projectKey => $projectValue) {
            // ToDo
            // if (
            //     ($this->ebBackendService->beUserIsAdmin) ||
            //     (
            //         $this->ebBackendService->userHasBackendGroup(
            //             (int)($vendorValue['backendGroupId'] ?? 0)
            //         )
            //     )
            // ) {
            $projectCount++;
            // }
        }

        if ($projectCount > 0) {
            if ($isTypo3V14OrHigher) {
                $projectDropdown = $this->componentFactory->createDropDownButton();
            } else {
                // @extensionScannerIgnoreLine
                $projectDropdown = $buttonBar->makeDropDownButton();
            }

            $projectDropdown
                ->setLabel('Project') // ToDo LLL
                ->setTitle('Project') // ToDo LLL
                ->setShowLabelText(true);

            if ($isTypo3V14OrHigher) {
                $item = $this->componentFactory->createDropDownRadio();
            } else {
                $item = GeneralUtility::makeInstance(DropDownRadio::class);
            }

            $item
                ->setHref(
                    $this->uriBuilder->uriFor(
                        'list',
                        ['currentProject' => 'no'],
                        'Extension'
                    )
                )
                ->setLabel('No project') // ToDo LLL
                ->setTitle('No project')
                ->setActive($activeProject === 'no');

            $projectDropdown->addItem($item);

            foreach ($this->ebBackendService->projects as $projectKey => $projectValue) {
                if ($isTypo3V14OrHigher) {
                    $item = $this->componentFactory->createDropDownRadio();
                } else {
                    $item = GeneralUtility::makeInstance(DropDownRadio::class);
                }

                $item
                    ->setHref(
                        $this->uriBuilder->uriFor(
                            'list',
                            ['currentProject' => $projectKey],
                            'Extension'
                        )
                    )
                    ->setLabel((string)$projectValue['name'])
                    ->setTitle((string)$projectValue['name'])
                    ->setActive($projectKey === $activeProject);

                $projectDropdown->addItem($item);
            }

            $buttonBar->addButton(
                $projectDropdown,
                ButtonBar::BUTTON_POSITION_LEFT,
                1
            );
        }

        // Show if vendors are present

        $visibleVendors = [];

        foreach ($this->ebBackendService->vendors as $vendorKey => $vendorValue) {
            if (
                $this->ebBackendService->beUserIsAdmin
                || $this->ebBackendService->userHasBackendGroup(
                    (int)($vendorValue['backendGroupId'] ?? 0)
                )
            ) {
                $visibleVendors[$vendorKey] = $vendorValue;
            }
        }

        $vendorCount = count($visibleVendors);

        if ($vendorCount > 0) {
            if ($isTypo3V14OrHigher) {
                $vendorDropdown = $this->componentFactory->createDropDownButton();
            } else {
                // @extensionScannerIgnoreLine
                $vendorDropdown = $buttonBar->makeDropDownButton();
            }

            $vendorDropdown
                ->setLabel('Vendor') // ToDo LLL
                ->setTitle('Vendor') // ToDo LLL
                ->setShowLabelText(true);

            if ($vendorCount > 1 && $projectCount > 0) {
                if ($isTypo3V14OrHigher) {
                    $item = $this->componentFactory->createDropDownRadio();
                } else {
                    $item = GeneralUtility::makeInstance(DropDownRadio::class);
                }

                $item
                    ->setHref(
                        $this->uriBuilder->uriFor(
                            'list',
                            [
                                'currentVendor' => 'no',
                                'currentProject' => $activeProject,
                            ],
                            'Extension'
                        )
                    )
                    ->setLabel('Show no vendors') // ToDo LLL
                    ->setTitle('Show no vendors')
                    ->setActive($activeVendor === 'no');

                $vendorDropdown->addItem($item);
            }

            if ($vendorCount > 1) {
                if ($isTypo3V14OrHigher) {
                    $item = $this->componentFactory->createDropDownRadio();
                } else {
                    $item = GeneralUtility::makeInstance(DropDownRadio::class);
                }

                $item
                    ->setHref(
                        $this->uriBuilder->uriFor(
                            'list',
                            [
                                'currentVendor' => 'all',
                                'currentProject' => $activeProject,
                            ],
                            'Extension'
                        )
                    )
                    ->setLabel('Show all vendors') // ToDo LLL
                    ->setTitle('Show all vendors')
                    ->setActive($activeVendor === 'all');

                $vendorDropdown->addItem($item);
            }

            foreach ($visibleVendors as $vendorKey => $vendorValue) {
                if ($isTypo3V14OrHigher) {
                    $item = $this->componentFactory->createDropDownRadio();
                } else {
                    $item = GeneralUtility::makeInstance(DropDownRadio::class);
                }

                $item
                    ->setHref(
                        $this->uriBuilder->uriFor(
                            'list',
                            [
                                'currentVendor' => $vendorKey,
                                'currentProject' => $activeProject,
                            ],
                            'Extension'
                        )
                    )
                    ->setLabel((string)$vendorValue['vendorName'])
                    ->setTitle((string)$vendorValue['vendorName'])
                    ->setActive($vendorKey === $activeVendor);

                $vendorDropdown->addItem($item);
            }

            $buttonBar->addButton(
                $vendorDropdown,
                ButtonBar::BUTTON_POSITION_LEFT,
                2
            );
        }
    }

    /**
     * @since 0.12
     */
    final public function addDocHeaderCloseButton(
        string $action,
        string $controller,
        string $vendorName = '',
        string $extensionName = '',
        string $projectKey = '',
        string $componentsName = '',
        string $componentName = '',
    ): void {
        $languageService = self::getLanguageService();

        $buttonBar = $this->moduleTemplate
            ->getDocHeaderComponent()
            ->getButtonBar();

        $parameters = [];

        if ($vendorName) {
            $parameters['vendorName'] = $vendorName;
        }

        if ($extensionName) {
            $parameters['extensionName'] = $extensionName;
        }

        if ($projectKey) {
            $parameters['projectKey'] = $projectKey;
        }

        if ($componentsName) {
            $parameters['componentsName'] = $componentsName;
        }

        if ($componentName) {
            $parameters['componentName'] = $componentName;
        }

        $icon = $this->iconFactory->getIcon(
            'actions-close',
            IconSize::SMALL
        );

        if (
            version_compare(
                VersionNumberUtility::getNumericTypo3Version(),
                '14.0.0',
                '>='
            )
        ) {
            $closeButton = $this->componentFactory->createLinkButton();
        } else {
            // @extensionScannerIgnoreLine
            $closeButton = $buttonBar->makeLinkButton();
        }

        $closeButton
            ->setTitle(
                $languageService->sL(
                    $this->ebBackendService->lll . '.xlf:close'
                )
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                $this->uriBuilder->uriFor(
                    $action,
                    $parameters,
                    $controller
                )
            )
            ->setDataAttributes([
                'hotkey-action' => 'close',
            ]);

        $buttonBar->addButton(
            $closeButton,
            ButtonBar::BUTTON_POSITION_LEFT,
            2
        );
    }

    /**
     * @since 0.12
     */
    final public function addDocHeaderSaveButton(
        string $saveFromId,
        string $saveController,
    ): void {
        $languageService = self::getLanguageService();

        $buttonBar = $this->moduleTemplate
            ->getDocHeaderComponent()
            ->getButtonBar();

        $icon = $this->iconFactory->getIcon(
            'actions-save',
            IconSize::SMALL
        );

        if (
            version_compare(
                VersionNumberUtility::getNumericTypo3Version(),
                '14.0.0',
                '>='
            )
        ) {
            $saveButton = $this->componentFactory->createInputButton();
        } else {
            // @extensionScannerIgnoreLine
            $saveButton = $buttonBar->makeInputButton();
        }

        $saveButton
            ->setTitle(
                $languageService->sL(
                    $this->ebBackendService->lll . '.xlf:save'
                )
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setName('cmd')
            ->setValue('save')
            ->setForm($saveFromId)
            ->setDataAttributes([
                'hotkey-action' => 'save',
            ]);

        $buttonBar->addButton(
            $saveButton,
            ButtonBar::BUTTON_POSITION_LEFT,
            3
        );
    }

    /**
     * @since 0.12
     */
    final public function addDocHeaderAddButton(
        string $action,
        string $controller,
        array $parameters = [],
    ): void {
        $languageService = self::getLanguageService();

        $buttonBar = $this->moduleTemplate
            ->getDocHeaderComponent()
            ->getButtonBar();

        $icon = $this->iconFactory->getIcon(
            'actions-archive',
            IconSize::SMALL
        );

        $lll
            = $this->ebBackendService->lll
            . '.'
            . strtolower($controller)
            . '.xlf:link.'
            . strtolower($action);

        if (
            version_compare(
                VersionNumberUtility::getNumericTypo3Version(),
                '14.0.0',
                '>='
            )
        ) {
            $linkButton = $this->componentFactory->createLinkButton();
        } else {
            // @extensionScannerIgnoreLine
            $linkButton = $buttonBar->makeLinkButton();
        }

        $linkButton
            ->setTitle($languageService->sL($lll))
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                $this->uriBuilder->uriFor(
                    $action,
                    $parameters,
                    $controller
                )
            )
            ->setDataAttributes([
                'hotkey-action' => 'add',
            ]);

        $buttonBar->addButton(
            $linkButton,
            ButtonBar::BUTTON_POSITION_LEFT,
            3
        );
    }

    /**
     * @since 0.12
     */
    final public function addDocHeaderBuildButton(
        string $action,
        string $controller,
        string $vendorName,
        string $extensionName,
    ): void {
        $languageService = self::getLanguageService();

        $buttonBar = $this->moduleTemplate
            ->getDocHeaderComponent()
            ->getButtonBar();

        $parameters = [];

        if ($vendorName) {
            $parameters['vendorName'] = $vendorName;
        }

        if ($extensionName) {
            $parameters['extensionName'] = $extensionName;
        }

        $icon = $this->iconFactory->getIcon(
            'actions-play',
            IconSize::SMALL
        );

        $lll
            = $this->ebBackendService->lll
            . '.'
            . strtolower($controller)
            . '.xlf:link.'
            . strtolower($action);

        if (
            version_compare(
                VersionNumberUtility::getNumericTypo3Version(),
                '14.0.0',
                '>='
            )
        ) {
            $linkButton = $this->componentFactory->createLinkButton();
        } else {
            // @extensionScannerIgnoreLine
            $linkButton = $buttonBar->makeLinkButton();
        }

/**
// ToDo V14 selector
        $linkButton = $this->componentFactory
         xTag('button')
            ->setLabel($languageService->sL($lll))
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setAttributes([
                'type' => 'button',
                'hotkey-action' => 'build',
                'data-extensionbuilder-build-button' => '1',
                'data-vendor-name' => $vendorName,
                'data-extension-name' => $extensionName,
            ]);
*/
// ToDo V13 selector
        $linkButton = GeneralUtility::makeInstance(GenericButton::class)
            ->setTag('button')
            ->setLabel($languageService->sL($lll))
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setAttributes([
                'type' => 'button',
                'data-hotkey-action' => 'build',
                'data-extensionbuilder-build-button' => '1',
                'data-vendor-name' => $vendorName,
                'data-extension-name' => $extensionName,
            ]);

        $buttonBar->addButton(
            $linkButton,
            ButtonBar::BUTTON_POSITION_LEFT,
            4
        );
    }

    /**
     * @since 0.12
     */
    final public function addDocHeaderImportExampleVendorToDoRemove(
        string $importAction,
        string $importController,
    ): void {
        // ToDo Refactoring

        $languageService = self::getLanguageService();

        $buttonBar = $this->moduleTemplate
            ->getDocHeaderComponent()
            ->getButtonBar();

        $icon = $this->iconFactory->getIcon(
            'actions-archive',
            IconSize::SMALL
        );

        if (
            version_compare(
                VersionNumberUtility::getNumericTypo3Version(),
                '14.0.0',
                '>='
            )
        ) {
            $addButton = $this->componentFactory->createLinkButton();
        } else {
            // @extensionScannerIgnoreLine
            $addButton = $buttonBar->makeLinkButton();
        }

        $addButton
            ->setTitle(
                $languageService->sL(
                    $this->ebBackendService->lll
                    . '.vendor.xlf:importExample'
                )
            )
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                $this->uriBuilder->uriFor(
                    $importAction,
                    [],
                    $importController
                )
            );

        $buttonBar->addButton(
            $addButton,
            ButtonBar::BUTTON_POSITION_LEFT,
            4
        );
    }

    /**
     * @since 0.12
     */
    final public function flashMessage(
        string $flashMessage1,
        string $flashMessage2,
        ContextualFeedbackSeverity $feedback = ContextualFeedbackSeverity::OK
    ): void {
        $flashMessageService = GeneralUtility::makeInstance(
            FlashMessageService::class
        );

        $notificationQueue
            = $flashMessageService->getMessageQueueByIdentifier(
                FlashMessageQueue::NOTIFICATION_QUEUE
            );

        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $flashMessage1,
            $flashMessage2,
            $feedback,
        );

        $notificationQueue->enqueue($flashMessage);
    }

    /**
     * @since 0.12
     */
    final public function addDocHeaderTypo3Command(
        string $action,
        string $controller,
        string $vendorName = '',
        string $extensionName = '',
        string $projectKey = '',
        string $componentsName = '',
        string $componentName = '',
    ): void {
        $buttonBar = $this->moduleTemplate
            ->getDocHeaderComponent()
            ->getButtonBar();

        $parameters = [];

        if ($vendorName) {
            $parameters['vendorName'] = $vendorName;
        }

        if ($extensionName) {
            $parameters['extensionName'] = $extensionName;
        }

        if ($projectKey) {
            $parameters['projectKey'] = $projectKey;
        }

        if ($componentsName) {
            $parameters['componentsName'] = $componentsName;
        }

        if ($componentName) {
            $parameters['componentName'] = $componentName;
        }

        $icon = $this->iconFactory->getIcon(
            'actions-close',
            IconSize::SMALL
        );

        if (
            version_compare(
                VersionNumberUtility::getNumericTypo3Version(),
                '14.0.0',
                '>='
            )
        ) {
            $typo3CommandButton
                = $this->componentFactory->createInputButton();
        } else {
            // @extensionScannerIgnoreLine
            $typo3CommandButton = $buttonBar->makeLinkButton();
        }

        $typo3CommandButton
            ->setTitle('CMD')
            ->setShowLabelText(true)
            ->setIcon($icon)
            ->setHref(
                $this->uriBuilder->uriFor(
                    $action,
                    $parameters,
                    $controller
                )
            );

        $buttonBar->addButton(
            $typo3CommandButton,
            ButtonBar::BUTTON_POSITION_LEFT,
            2
        );
    }
}