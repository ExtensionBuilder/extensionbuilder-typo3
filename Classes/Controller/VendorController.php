<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Core\Environment;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class VendorController extends ExtensionBuilderController
{

    /*
     * ToDo
     *  - Duplicate
     *  - Rename
     */
 
    final function listAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorList' => $this->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );
        if (($this->vendors ?? false) && !($this->extensionbuilderObject->vendorsAndExtensions ?? false)) {
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

    final function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorName = $bodyParams['vendorData']['vendorName'] ?? '';
                $vendorData = $bodyParams['vendorData'];

                if ($vendorName) {
                    if (!($this->vendors[$vendorName] ?? false)) {

                        $this->vendors[$vendorName] = $vendorData;
                        $this->noVendors = false;

                        $this->writeVendor($vendorName);
                        $this->flashMessage(
                            '',
                            $this->getTranslatedLabel(
                                $this->request,
                                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:savingVendor'
                            ) . $vendorName,
                        );

                        return $this->redirect('list', 'Vendor');
                    } else {
                        $this->flashMessage('', 'Vendor name exists please change'); // ToDo LLL
                    }
                } else {
                    $this->flashMessage('', 'Please specify vendor name'); // ToDo LLL
			    }
                break;
		}

        if (!($vendorData ?? false)) {
            $vendorData = [];
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
            'vendor-add-form',
        );

        return $this->moduleTemplate->renderResponse('Vendor/Add');
    }

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $vendorData = $this->vendors[$vendorName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':

                Tools\ConfigArray::arrayMerge($vendorData, $bodyParams['vendorData']);

                $this->vendors[$vendorName] = $vendorData;

                $this->writeVendor($vendorName);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:savingVendor'
                    ) . $vendorName,
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
            'vendor-edit-form',
        );

    	return $this->moduleTemplate->renderResponse('Vendor/Edit');
    }

    final function duplicateAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorNameOrg = $bodyParams['vendorName'];
        $vendorData = $this->vendors[$vendorNameOrg];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorNameNew = $bodyParams['vendorData']['vendorName'];

                $this->vendors[$vendorNameNew] = $this->vendors[$vendorNameOrg];
                $this->vendors[$vendorNameNew]['vendorName'] = $vendorNameNew;

                $this->writeVendor($vendorNameNew);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:duplicateVendor'
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

    final function renameAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';

        $vendorData = $this->vendors[$vendorName];

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
//                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:renameVendor'
//                    ) . $vendorName,
//                );

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
            'vendor-rename-form',
        );

    	return $this->moduleTemplate->renderResponse('Vendor/Rename');
    }

    final function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->deleteVendor($bodyParams['vendorName']);
        $this->readVendor();

        // Delete ExampleVendor project entry 
        if ($bodyParams['vendorName'] == 'ExampleVendor') {
            foreach($this->projects ?? [] as $projectUi => $projectData) {
                if ($projectData['name'] == 'Example Vendor') {
                    unset($this->projects[$projectUi]);
                    $this->writeProject();
                    break;
                }
			}
        }

        if (!($this->vendors ?? false)) {
            $this->noVendors = true;
        }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorList' => $this->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Vendor',
        );

        if (!($this->noVendors)) {
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

        Tools\ZipArchive::unzip(
            Environment::getPublicPath() . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR
            . 'extensionbuilder_typo3' . DIRECTORY_SEPARATOR
            .'ExampleVendor.zip',
            Environment::getPublicPath() . DIRECTORY_SEPARATOR
            . 'fileadmin' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR,
        );

        $this->readVendor();
        $this->readVendorsAndExtensions();

        // Add ExampleVendor project entry
        $projectKey = uniqid();
        if (!($this->projects[$projectKey] ?? false)) {
            $project = [];
            $project['name'] = 'Example Vendor';
            $project['description'] = 'Test';
            $project['extensions'] = [];
            foreach($this->vendorsAndExtensions['ExampleVendor']['extensions'] ?? [] as $extensionName => $extensionData) {
                $project['extensions'][$extensionName] = $extensionData;
                $project['extensions'][$extensionName]['extensionOnOff'] = true;
            }
            $this->projects[$projectKey] = $project;
            $this->writeProject();
		}

        return $this->redirect('list', 'Vendor');
	}

}