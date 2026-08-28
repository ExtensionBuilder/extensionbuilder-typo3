<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */

#[AsController]
final class VendorController extends ExtensionBuilderController
{

    /**
     * @since 0.12
     */
    final function listAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        return $this->vendorList();
    }

    /**
     * @since 0.12
     */
    private function vendorList(): ResponseInterface {

        if ($this->ebBackendService->noVendors) {
            $this->moduleTemplate->addFlashMessage(
                '', // ToDo LLL
                'To create your first extension, you must create a vendor.', // ToDo LLL
                ContextualFeedbackSeverity::INFO,
                true
            );
        }

        if ($this->ebBackendService->beUserIsAdmin) {
            $vendors = $this->ebBackendService->vendors;
        } else {
            $vendors = [];

            foreach ($this->ebBackendService->vendors as $vendorKey => $vendorValue) {
                if ($this->ebBackendService->userHasBackendGroup((int)($vendorValue['backendGroupId'] ?? 0))) {
                    $vendors[$vendorKey] = $vendorValue;
                }
            }
        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorList' => $vendors,
        ]);

        $this->addDocHeaderModuleDropDown('Vendor');
        if (($this->ebBackendService->vendors ?? false) && ($this->ebBackendService->vendorsAndExtensions ?? false)) {
            $this->addDocHeaderCloseButton(
                'list',
                'Extension',
            );
        }
        $this->addDocHeaderAddButton(
            'add',
            'Vendor',
        );

        return $this->moduleTemplate->renderResponse('VendorList');
	}

    /**
     * @since 0.12
     */
    final function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorData = $bodyParams['vendorData'];

                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->vendorConfiguration['fieldsEdit'],
                    $vendorData,
                );

                $vendorData['vendorName'] = preg_replace("/[^a-zA-Z0-9]/", '', trim($vendorData['vendorName'] ?? ''));

                $vendorName = $vendorData['vendorName'];

                if ($vendorName) {
                    if ($this->ebBackendService->vendors[$vendorName] ?? false) {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebBackendService->lll . '.vendor.xlf:vendornameexists')
                        );
                        break;
                    }

                    if (!($vendorData['vendorComposerName'] ?? false)) {
                        $vendorData['vendorComposerName'] = $vendorName;
                    }

// ToDo: Move to JS?
// ToDo: Check vendorComposerName already exists

                    $vendorComposerName = str_replace([' ','-'], '_', $vendorData['vendorComposerName']);
                    $vendorComposerName = ltrim($vendorComposerName, '1234567890');
                    $vendorComposerName = GeneralUtility::underscoredToUpperCamelCase(trim($vendorComposerName));
                    $vendorData['vendorComposerName'] = $vendorComposerName;
 
                    $vendorData['vendorId'] = Tools\Uuid::uuid();

                    $this->ebBackendService->vendors[$vendorName] = $vendorData;
                    $this->ebBackendService->noVendors = false;

                    $this->ebBackendService->writeVendor($vendorName);
                    $this->ebBackendService->readVendors();
                    $this->ebBackendService->addVednorIdToBackendUser(
                        (int)($this->ebBackendService->vendors[$vendorName]['backendGroupId'] ?? '')
                    );

                    $this->flashMessage(
                        '',
                        $this->getTranslatedLabel(
                            $this->request,
                            $this->ebBackendService->lll . '.vendor.xlf:savingVendor'
                         ) . $vendorName,
                    );
                } else {
                    $this->flashMessage(
                        '',
                        LocalizationUtility::translate($this->ebBackendService->lll . '.vendor.xlf:specifyvendorname')
                    );
			    }
                break;
		}

        if (!($vendorData ?? false)) { $vendorData = []; }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorData' => $vendorData,
            'vendorConfiguration' => $this->ebBackendService->vendorConfiguration,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Vendor',
        );
        $this->addDocHeaderSaveButton(
            'vendor-add-form',
            'Vendor',
        );

        return $this->moduleTemplate->renderResponse('VendorAdd');
    }

    /**
     * @since 0.12
     */
    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $vendorName = $bodyParams['vendorName'] ?? '';

        try {
            $this->ebBackendService->assertCanAccessVendor($vendorName);
        } catch (\RuntimeException $exception) {
            $this->flashMessage(
				$this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.vendor.xlf:noAccess.info1'
                ),
				$this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.vendor.xlf:noAccess.info2'
                ) . $vendorName,
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->vendorList();
        }

        $vendorData = $this->ebBackendService->vendors[$vendorName];

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->vendorConfiguration['fieldsEdit'],
                    $bodyParams['vendorData'],
                );

                Tools\ConfigArray::arrayMerge($vendorData, $bodyParams['vendorData']);

                $this->ebBackendService->vendors[$vendorName] = $vendorData;
                $this->ebBackendService->writeVendor($vendorName);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebBackendService->lll . '.vendor.xlf:savingVendor'
                    ) . $vendorName,
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorData' => $vendorData,
            'vendorConfiguration' => $this->ebBackendService->vendorConfiguration,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Vendor',
        );
        $this->addDocHeaderSaveButton(
            'vendor-edit-form',
            'Vendor',
        );

    	return $this->moduleTemplate->renderResponse('VendorEdit');
    }

    /**
     * @since 0.12
     */
    final function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';

        try {
            $this->ebBackendService->assertCanAccessVendor($vendorName);
        } catch (\RuntimeException $exception) {
            $this->flashMessage(
				$this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.vendor.xlf:noAccess.info1'
                ),
				$this->getTranslatedLabel(
                    $this->request,
                    $this->ebBackendService->lll . '.vendor.xlf:noAccess.info2'
                ) . $vendorName,
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->vendorList();
        }

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->ebBackendService->deleteVendor($vendorName);
        $this->ebBackendService->readVendors();

        return $this->vendorList();
    }

}