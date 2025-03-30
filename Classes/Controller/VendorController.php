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
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorList' => $this->ebService->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );

        if (($this->ebService->vendors ?? false) && ($this->ebService->vendorsAndExtensions ?? false)) {
            $this->addDocHeaderCloseButtons(
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
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorName = $bodyParams['vendorData']['vendorName'] ?? '';
                $vendorData = $bodyParams['vendorData'];

                if ($vendorName) {
                    if (!($this->vendors[$vendorName] ?? false)) {
debug($vendorName,'test');
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

                        return $this->redirect('list', 'Vendor');
                    } else {
debug(LocalizationUtility::translate($this->ebService->lll .'.vendor.xlf:vendornameexists'));
                        $this->flashMessage('', LocalizationUtility::translate($this->ebService->lll . '.vendor.xlf:vendornameexists'));
                    }
                } else {
                    $this->flashMessage('', LocalizationUtility::translate($this->ebService->lll . '.vendor.xlf:specifyvendorname'));
			    }
                break;
		}

        if (!($vendorData ?? false)) { $vendorData = []; }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Vendor',
            'vendor-add-form',
        );

        return $this->moduleTemplate->renderResponse('Vendor/Add');
    }

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
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

                return $this->redirect('list', 'Vendor');
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Vendor',
            'vendor-edit-form',
        );

    	return $this->moduleTemplate->renderResponse('Vendor/Edit');
    }

    final function duplicateActionToDo(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorNameOrg = $bodyParams['vendorName'];
        $vendorData = $this->vendors[$vendorNameOrg];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorNameNew = $bodyParams['vendorData']['vendorName'];

                $this->ebService->vendors[$vendorNameNew] = $this->vendors[$vendorNameOrg];
                $this->ebService->vendors[$vendorNameNew]['vendorName'] = $vendorNameNew;

                $this->ebService->writeVendor($vendorNameNew);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebService->lll . '.vendor.xlf:duplicateVendor'
                    ) . $bodyParams['vendorData']['vendorName'],
                );

                return $this->redirect('list', 'Vendor');
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Vendor',
            'vendor-duplicate-form',
        );

    	return $this->moduleTemplate->renderResponse('Vendor/Duplicate');
    }

    final function renameActionToDo(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';

        $vendorData = $this->ebService->vendors[$vendorName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':

//                Tools\ConfigArray::arrayMerge($vendorData, $parsedBody['vendorData']);

//$this->writeVendor($vendorName);
                $this->flashMessage(
                    '',
                    'not yet implemented',
                );

//                $this->flashMessage(
//                    '',
//                    $this->getTranslatedLabel(
//                        $request,
//                        $this->ebService->lll . '.vendor.xlf:renameVendor'
//                    ) . $vendorName,
//                );

                return $this->redirect('list', 'Vendor');
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Vendor',
            'vendor-rename-form',
        );

    	return $this->moduleTemplate->renderResponse('Vendor/Rename');
    }

    final function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
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

        if (!($this->ebService->vendors ?? false)) {
            $this->ebService->noVendors = true;
        }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorList' => $this->ebService->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );

        if (!($this->ebService->noVendors)) {
            $this->addDocHeaderCloseButtons(
                'list',
                'Extension',
            );
        }
        $this->addDocHeaderAddButton(
            'add',
		    'Vendor',
        );
        if (!($this->vendors['ExampleVendor'] ?? false)) { // ToDo ein und ausschalten über config
            $this->addDocHeaderImportExampleVendor(
                'importExampleVendor',
                'Vendor',
            );
		}

    	return $this->moduleTemplate->renderResponse('Vendor/List');
    }

    final function importExampleVendorAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

		$this->ebService->importExampleVendor();

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorList' => $this->ebService->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );

        if (($this->ebService->vendors ?? false) && ($this->ebService->vendorsAndExtensions ?? false)) {
            $this->addDocHeaderCloseButtons(
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

//        return $this->redirect('list', 'Vendor');
	}

}