<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class VendorController extends ExtensionBuilderController
{

    final function listAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $this->vendorList();
    }

    private function vendorList(): ResponseInterface {
        if (!($this->ebService->vendors ?? false)) { $this->ebService->noVendors = true; }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorList' => $this->ebService->vendors,
        ]);

        $this->addDocHeaderModuleDropDown('Vendor');
        if (($this->ebService->vendors ?? false) && ($this->ebService->vendorsAndExtensions ?? false)) {
            $this->addDocHeaderCloseButton(
                'list',
                'Extension',
            );
        }
        $this->addDocHeaderAddButton(
            'add',
            'Vendor',
        );

        if (
            ($this->ebService->configuration['importExample'] ?? false) &&
            (!($this->ebService->vendors['ExampleVendor'] ?? false))
        ) {
            $this->addDocHeaderImportExampleVendor(
                'importExampleVendor',
                'Vendor',
            );
		}

        return $this->moduleTemplate->renderResponse('Vendor/List');
	}

    final function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorName = $bodyParams['vendorData']['vendorName'] ?? '';
                $vendorData = $bodyParams['vendorData'];

                if ($vendorName) {
                    if (!($this->vendors[$vendorName] ?? false)) {
                        $this->ebService->vendors[$vendorName] = $vendorData;
                        $this->ebService->noVendors = false;

                        $this->ebService->writeVendor($vendorName);
                        $this->ebService->readVendor();
                        $this->flashMessage(
                            '',
                            $this->getTranslatedLabel(
                                $this->request,
                                $this->ebService->lll . '.vendor.xlf:savingVendor'
                            ) . $vendorName,
                        );
                    } else {
                        $this->flashMessage('', LocalizationUtility::translate($this->ebService->lll . '.vendor.xlf:vendornameexists'));
                    }
                } else {
                    $this->flashMessage('', LocalizationUtility::translate($this->ebService->lll . '.vendor.xlf:specifyvendorname'));
			    }
                break;
		}

        if (!($vendorData ?? false)) { $vendorData = []; }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorData' => $vendorData,
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

        return $this->moduleTemplate->renderResponse('Vendor/Add');
    }

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $vendorData = $this->ebService->vendors[$vendorName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($vendorData, $bodyParams['vendorData']);
                $this->ebService->vendors[$vendorName] = $vendorData;
                $this->ebService->writeVendor($vendorName);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebService->lll . '.vendor.xlf:savingVendor'
                    ) . $vendorName,
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorData' => $vendorData,
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

    	return $this->moduleTemplate->renderResponse('Vendor/Edit');
    }

    final function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->ebService->deleteVendor($bodyParams['vendorName']);
        $this->ebService->readVendor();

        // Delete ExampleVendor project entry
        if ($bodyParams['vendorName'] == 'ExampleVendor') {
            foreach($this->ebService->projects ?? [] as $projectUi => $projectData) {
                if ($projectData['name'] == 'Example Vendor') {
                    unset($this->ebService->projects[$projectUi]);
                    $this->ebService->writeProject();
                    break;
                }
			}
        }

        return $this->vendorList();
    }

    final function importExampleVendorAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

		$this->ebService->importExampleVendor();

        return $this->vendorList();
	}

}