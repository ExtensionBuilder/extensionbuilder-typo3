<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Service\ExtensionLockService;
use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
#[AsController]
final class ExtensionController extends ExtensionBuilderController
{
    /**
     * @since 0.12
     */
    public function listAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        // ToDo maintenance mode
        $maintenanceActive = false;
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/maintenancemodal.js');

        $this->pageRenderer->addInlineSettingArray('myext', [
            'maintenance' => [
                'active' => $maintenanceActive,
                'message' => 'Wartung aktiv: Bitte gerade nichts Kritisches ändern.',
            ],
        ]);

        $this->moduleTemplate->assignMultiple([
            'maintenanceActive' => $maintenanceActive,
        ]);

        if (
            ($bodyParams['currentProject'] ?? false)
            && !($bodyParams['currentProject'] == ($this->ebBackendService->developer['typo3']['currentProject'] ?? ''))
        ) {
            $this->ebBackendService->developer['typo3']['currentProject'] = $bodyParams['currentProject'];
            $this->ebBackendService->writeDeveloper();
        }

        if (
            ($bodyParams['currentVendor'] ?? false)
            && !($bodyParams['currentVendor'] == ($this->ebBackendService->developer['typo3']['currentVendor'] ?? ''))
        ) {
            $this->ebBackendService->developer['typo3']['currentVendor'] = $bodyParams['currentVendor'];
            $this->ebBackendService->writeDeveloper();
        }

        return $this->extensionList();
    }

    /**
     * @since 0.12
     */
    private function extensionList(): ResponseInterface
    {
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildmodal.js');

        // There is no developer forwarding to create this
        if ($this->ebBackendService->noDeveloper) {
            return $this->redirect('edit', 'Developer');
        }

        // There is no vendor forwarding to create this
        if ($this->ebBackendService->noVendors) {
            return $this->redirect('list', 'Vendor');
        }

        $projects = [];
        $currentProject = '';
        $vendors = [];
        $currentVendor = '';

        $projects = $this->ebBackendService->projects;

        //
        if ($this->ebBackendService->beUserIsAdmin) {

            $project = $this->ebBackendService->projects[($this->ebBackendService->developer['typo3']['currentProject'] ?? 'no')] ?? [];
            //            $projects = $this->ebBackendService->projects[(
            //                $this->ebBackendService->developer['typo3']['currentProject'] ?? 'no'
            //            )] ?? [];

            $currentProject = $this->ebBackendService->developer['typo3']['currentProject'] ?? 'no';

            $vendors = $this->ebBackendService->vendorsAndExtensions ?? [];
            $currentVendor = $this->ebBackendService->developer['typo3']['currentVendor'] ?? 'all';

        } else {
            $currentProject = $this->ebBackendService->developer['typo3']['currentProject'] ?? 'no';
            $currentVendor = $this->ebBackendService->developer['typo3']['currentVendor'] ?? 'all';

            //            $projects = ['no'];
            $project = $this->ebBackendService->projects[($this->ebBackendService->developer['typo3']['currentProject'] ?? 'no')] ?? [];

            // ToDo $project
            foreach ($this->ebBackendService->vendors as $vendorKey => $vendorValue) {
                if ($this->ebBackendService->userHasBackendGroup((int)($vendorValue['backendGroupId'] ?? 0))) {
                    // ToDo
                }
            }
            //            foreach ($this->ebBackendService->vendors as $vendorKey => $vendorValue) {
            //                if (strpos($this->ebBackendService->beUserGroup, ((string)$vendorValue['backendGroupId'] ?? ''))) {
            //                    $vendors[$vendorKey] = $this->ebBackendService->vendorsAndExtensions[$vendorKey];
            //				}
            //            }

        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'isProKey' => $this->isProKey,
            'components' =>  $this->ebBackendService->extensionConfiguration['components'],
            'projects' =>  $projects,
            'project' =>  $project,
            'currentProject' => $currentProject,
            'vendors' => $vendors,
            'currentVendor' => $currentVendor,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Extension',
            activeProject: $currentProject,
            activeVendor: $currentVendor,
        );
        $this->addDocHeaderAddButton(
            'add',
            'Extension',
        );
        return $this->moduleTemplate->renderResponse('Extension/List');
    }

    /**
     * @since 0.12
     */
    public function addAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfieldschanged.js');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $extensionData = $bodyParams['extensionData'] ?? [];

                $vendorName = (string)($extensionData['vendorName'] ?? '');
                $extensionName = (string)($extensionData['extensionNamespace'] ?? '');

                // ToDo: Move to JSA
                // ToDo:check vendorComposerName already exists

                if ($vendorName && $extensionName) {
                    //ToDo
                    if (!($this->ebBackendService->localExtensions[$extensionName] ?? false)) {
                        // ToDo
                        //                        $extensionNamespace = str_replace([' ','-'], '_', $extensionData['extensionNamespace']);
                        //                        $extensionNamespace = ltrim($extensionNamespace, '1234567890');
                        //                        $extensionNamespace = GeneralUtility::underscoredToUpperCamelCase(trim($extensionNamespace));
                        // ToDo
                        //                        $extensionData['vendorComposerName'] = $this->ebBackendService->configuration['vendorComposerName'];

                        $extensionData['ebDevSystemId'] = $this->ebBackendService->configuration['systemId'] ?? '';
                        $extensionData['ebDevVendorId'] = $this->ebBackendService->vendors[$vendorName]['vendorId' ?? ''];

                        $extensionData['ebDevDeveloperId'] = [];
                        $extensionData['ebDevDeveloperId'][$this->ebBackendService->developer['developerId']] = [];

                        $extensionData['ebDevExtensionId'] = Tools\Uuid::uuid();
                        $extensionData['ebDevJsonVersion'] = '1';

                        $extensionData['versionMajor'] = 0;
                        $extensionData['versionMinor'] = 1;
                        $extensionData['versionRevision'] = 0;

                        $this->ebBackendService->extension['extension'] = $extensionData;
                        // 8888
                        Tools\ConfigArray::checkFieldsToBool(
                            $this->ebBackendService->extensionConfiguration['fieldsEdit'],
                            $this->ebBackendService->extension['extension'],
                        );

                        $this->ebBackendService->writeExtension($vendorName, $extensionName);

                        $this->ebBackendService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension'] = [];
                        $this->ebBackendService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension'] = $extensionData;

                        $this->flashMessage('', 'Saving extension: ' . $extensionName); // ToDo LLL

                        return $this->extensionList();
                    }
                    if ($this->ebBackendService->isComposerMode) {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebBackendService->lll . '.extension.xlf:extensionexists')
                        );
                    } else {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebBackendService->lll . '.extension.xlf:extensionexists')
                        );
                    }

                } else {
                    if ($vendorName) {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebBackendService->lll . '.extension.xlf:specifyextensionname')
                        );
                    } else {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebBackendService->lll . '.extension.xlf:specifyvendorname')
                        );
                    }
                }
                break;
        }

        if (!($extension ?? false)) {
            $extension = [];
        }

        $selects = $this->ebBackendService->extensionConfiguration['selects'];
        $selects['vendors'] = $this->ebBackendService->getVendors();

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'extension' => $extension,
            'extensionConfiguration' => $this->ebBackendService->extensionConfiguration,
            'extensionSelects' => $selects,
            'selections' => $this->ebBackendService->extension['selections'] ?? [], // ToDo warum leer?
        ]);

        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderSaveButton(
            'extension-add-form',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Extension/Add');
    }

    /**
     * @since 0.12
     */
    public function editAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildmodal.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        try {
            $this->ebBackendService->assertCanAccessVendor($vendorName);
        } catch (\RuntimeException $exception) {
            $this->flashMessage(
                $this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.extension.xlf:noAccess.info1'
                ),
                $this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.extension.xlf:noAccess.info2'
                ) . $extensionName,
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->extensionList();
        }

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $extensionData = &$this->ebBackendService->extension;
        $componentsDev = $this->ebBackendService->extensionConfiguration['components'];

        if (($extensionData['extension']['type'] ?? '') === 'sitepackage') {
            foreach ($componentsDev ?? [] as $componentsDevKey => $componentsDevValue) {
                if (!($componentsDevValue['sitePackage'] ?? false)) {
                    unset($componentsDev[$componentsDevKey]);
                }
            }
        }

        //$lock = $this->extensionLockService->acquire(
        //    $vendorName,
        //    $extensionName,
        //    (int)$GLOBALS['BE_USER']->user['uid'],
        //    (string)$GLOBALS['BE_USER']->user['username']
        //);

        //$readOnly = !$lock['acquired'] && !$GLOBALS['BE_USER']->isAdmin();

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge(
                    $this->ebBackendService->extension['extension'],
                    $bodyParams['extensionData']
                );

                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->extensionConfiguration['fieldsEdit'],
                    $this->ebBackendService->extension['extension'],
                );

                $this->ebBackendService->extension['extension']['version']
                    = (string)($extensionData['versionMajor'] ?? '0') . '.'
                    . (string)($extensionData['versionMinor'] ?? '0') . '.'
                    . (string)($extensionData['versionRevision'] ?? '0');

                $this->ebBackendService->writeExtension($vendorName, $extensionName);

                $this->flashMessage('', 'Saving extension: ' . $extensionName); // ToDo LLL

                break;
        }

        // ToDo Arra in fluid
        unset($extensionData['authors']);
        unset($extensionData['support']);
        unset($extensionData['keywords']);
        unset($extensionData['depends']);

        //       $javaScriptRenderer = $this->pageRenderer->getJavaScriptRenderer();
        //       $javaScriptRenderer->addJavaScriptModuleInstruction(
        //           JavaScriptModuleInstruction::create('@typo3/filelist/file-list.js')->instance()
        //       );

        //$this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/tree-init.js');

        //$this->initializeModule($this->request);

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'extensionConfiguration' => $this->ebBackendService->extensionConfiguration,
            'configuration' => $this->ebBackendService->configuration,
            'componentsDev' =>  $componentsDev,
            'vendors' => $this->ebBackendService->getVendors(),
        ]);

        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderSaveButton(
            'extension-edit-form',
            'Extension',
        );
        $this->addDocHeaderBuildButton(
            'build',
            'Extension',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse('Extension/Edit');
    }

    /**
     * @since 0.12
     */
    public function deleteAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        try {
            $this->ebBackendService->assertCanAccessVendor($vendorName);
        } catch (\RuntimeException $exception) {
            $this->flashMessage(
                $this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.extension.xlf:noAccess.info1'
                ),
                $this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.extension.xlf:noAccess.info2'
                ) . $extensionName,
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->extensionList();
        }

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        $this->ebBackendService->deleteExtension($vendorName, $extensionName);

        if ($this->ebBackendService->projects[($this->ebBackendService->developer['typo3']['project'] ?? 'no')] ?? false) {
            unset($this->ebBackendService->projects[$this->ebBackendService->developer['typo3']['project']]['extensions'][$extensionName]);
            $this->ebBackendService->writeProjects();
            $this->ebBackendService->readProjects();
        }

        $this->flashMessage('', 'Extension: ' . $extensionName . ' is deleted'); // ToDo LLL

        return $this->redirect('list', 'Extension');
    }

    /**
     * @since 0.12
     */
    public function uploadAction(): ResponseInterface
    {
        // ToDo Refactory

        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $this->addDocHeaderModuleDropDown(
            'Extension',
        );

        $vendorData = $this->ebBackendService->vendorsAndExtensions[$vendorName];
        $extensionData = $this->ebBackendService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        // GtiHub
        if ($extensionData['extensionBuild']['gitHubCom'] ?? false) {
            $organization = $extensionData['extensionBuild']['gitHubCom']['vendor'] ?? '';
            $repo = $extensionName;
            $token = $extensionData['extensionBuild']['gitHubCom']['token'] ?? '';

            if (Tools\Github::checkOrganization($organization)) {
                if (Tools\Github::findRepo($organization, $repo)) {
                    $this->flashMessage('', 'Repro found'); // ToDo LLL

                    // ToDo Upload

                } else {
                    $this->flashMessage('', 'Repro not found'); // ToDo LLL
                }
            } else {
                $this->flashMessage('', 'No Repro '); // ToDo LLL
            }

        } else {
            $this->flashMessage('', 'No Github config'); // ToDo LLL
        }

        return $this->redirect('list', 'Extension');
    }
}