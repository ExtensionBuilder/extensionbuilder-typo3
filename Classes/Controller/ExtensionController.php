<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ExtensionController extends ExtensionBuilderController
{

    public function listAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

// $this->pageRenderer->loadJavaScriptModule('@typo3/rte-ckeditor/ckeditor5.js');
// JavaScriptRenderer->includeAllImports(),
// $this->pageRenderer->addJsFile('EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Backend/my-module.js');
// $this->pageRenderer->addJsFile('EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Modal.js');

        if (
            ($bodyParams['currentProject'] ?? false) &&
            !($bodyParams['currentProject'] == $this->ebService->developer['typo3']['project'])
        ) {
            $this->ebService->developer['typo3']['project'] = $bodyParams['currentProject'];
            $this->ebService->writeDeveloper();
		}

        if (
            ($bodyParams['currentVendor'] ?? false) &&
            !($bodyParams['currentVendor'] == $this->ebService->developer['typo3']['vendor'])
        ) {
            $this->ebService->developer['typo3']['vendor'] = $bodyParams['currentVendor'];
            $this->ebService->writeDeveloper();
		}

        return $this->extensionList();
    }

    private function extensionList(): ResponseInterface
    {
        // No developer exists ToDo 
        if ($this->ebService->noDeveloper) {
            return $this->redirect('edit', 'Developer');
        }
        // No vendor exists ToDo
        if (!($this->ebService->vendors)) {
            return $this->redirect('list', 'Vendor');
        }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'currentProject' => $this->ebService->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->ebService->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->ebService->projects[($this->ebService->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->ebService->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Extension',
            activeProjcet: $this->ebService->developer['typo3']['project'] ?? 'no',
            activeVendor: $this->ebService->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            'add',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Extension/List');
	}

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $vendorName = $bodyParams['extensionData']['extension']['vendorName'];
                $extensionName = $bodyParams['extensionData']['extension']['extensionName'];
                $extensionData = $bodyParams['extensionData'] ?? [];
                if ($vendorName && $extensionName) {
                    if (!($this->ebService->localExtensions[$extensionName] ?? false)) {

                        $extensionData['extension']['versionMajor'] = 0;
                        $extensionData['extension']['versionMinor'] = 1;
                        $extensionData['extension']['versionRevision'] = 0;

// $this->ebService->writeExtension($vendorName, $extensionName, $extensionData);

                        self::save(
                            $vendorName,
                            $extensionName,
                            $extensionData,
                        );

                        return $this->extensionList();
                    } else {

                        if ($this->ebService->isComposerMode) {
                            $this->flashMessage('', LocalizationUtility::translate($this->ebService->lll .'.extension.xlf:extensionexists'));
						} else {
                            $this->flashMessage('', LocalizationUtility::translate($this->ebService->lll .'.extension.xlf:extensionexists'));
						}
                    }
                } else {
                    if ($vendorName) {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebService->lll .'.extension.xlf:specifyextensionname')
                        );
					} else {
                        $this->flashMessage(
                            '',
                            LocalizationUtility::translate($this->ebService->lll .'.extension.xlf:specifyvendorname')
                        );
					}
			    }
                break;
		}
	
        if (!($extensionData ?? false)) {
            $extensionData = [];
        }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'projects' => $this->ebService->projects,
			'registeredVendorGroups' => $this->ebService->getRegisteredVendorGroups(),
            'extensionData' => $extensionData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Extension',
            'extension-add-form'
        );

    	return $this->moduleTemplate->renderResponse('Extension/Add');
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];

        $extensionData = $this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($extensionData,$bodyParams['extensionData']);

                self::save(
                    $bodyParams['vendorName'] ?? '',
                    $bodyParams['extensionName'] ?? '',
                    $extensionData ?? [],
                );

                return $this->extensionList();
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'registeredVendorGroups' => $this->ebService->getRegisteredVendorGroups(),
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
           'list',
           'Extension',
           'extension-edit-form',
        );

        return $this->moduleTemplate->renderResponse('Extension/Edit');
    }

    public function duplicateActionToDo(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $this->moduleTemplate->renderResponse('Extension/Duplicate');
    }

    public function renameActionToDo(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $this->moduleTemplate->renderResponse('Extension/Rename');
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $this->ebService->deleteExtension($vendorName, $extensionName);

        if ($this->ebService->projects[($this->developer['typo3']['project'] ?? 'no')] ?? false) {
            unset($this->ebService->projects[$this->developer['typo3']['project']]['extensions'][$extensionName]);
            $this->ebService->writeProject();
            $this->ebService->readProject();
        }

        $this->flashMessage('', 'Extension: ' . $extensionName . ' is deleted'); // ToDo LLL

        return $this->redirect('list', 'Extension');
    }

    public function buildAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $builderUri = $this->ebService->configuration['typo3']['builderUrl'];

        $copyInExtension = true;

        $flushT3andPhpCache = $this->ebService->developer['typo3']['flushT3andPhpCache'] ?? false;
        $analyzeDatabaseStructure = $this->ebService->developer['typo3']['analyzeDatabaseStructure'] ?? false;
        $rebuildPhpAutoload = $this->ebService->developer['typo3']['rebuildPhpAutoload'] ?? false;

        $this->ebService->build(
            $vendorName,
            $extensionName,
            $this->ebService->configuration,
            $this->ebService->developer,
        );

        $this->ebService->vendorsAndExtensions
            [$vendorName]['extensions'][$extensionName]['extensionBuild']['lastBuild'] = date('d-m-Y  h:i:m');
        $this->ebService->writeExtension($vendorName, $extensionName);

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'currentProject' => $this->ebService->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->ebService->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->ebService->projects[($this->ebService->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->ebService->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Extension',
            activeProjcet: $this->ebService->developer['typo3']['project'] ?? 'no',
            activeVendor: $this->ebService->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            'add',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Extension/List');
    }

    public function uploadActionToDo(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $this->addDocHeaderModuleDropDown(
            'Extension',
        );

        $vendorData = $this->ebService->vendorsAndExtensions[$vendorName];
        $extensionData = $this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

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

    // ------------------------------------------------------------------

// ToDO move to serice
	
    final function save(
        string $vendorName,
        string $extensionName,
        array $extensionData,
    ): void {

        $extensionData['extension']['version']  = (string)($extensionData['extension']['versionMajor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionMinor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionRevision'] ?? '0');

        $this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;

        $this->ebService->writeExtension($vendorName, $extensionName, $extensionData);

        $this->flashMessage('', 'Saving extension: ' . $extensionName); // ToDo LLL
    }

}