<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
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
		
//		JavaScriptRenderer->includeAllImports(),
//$this->pageRenderer->addJsFile('EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Backend/my-module.js');

//$this->pageRenderer->addJsFile('EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Modal.js');

// ToDo check for change
        if ($bodyParams['currentProject'] ?? false) {
            $this->developer['typo3']['project'] = $bodyParams['currentProject'];
            $this->writeDeveloper();
		}

// ToDo check for change
        if ($bodyParams['currentVendor'] ?? false) {
            $this->developer['typo3']['vendor'] = $bodyParams['currentVendor'];
            $this->writeDeveloper();
		}

        // No developer exists ToDo 
        if ($this->noDeveloper) {
            return $this->redirect('edit', 'Developer');
        }

        // No vendor exists ToDo
        if (!($this->vendors)) {
            return $this->redirect('list', 'Vendor');
        }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Extension',
            activeProjcet: $this->developer['typo3']['project'] ?? 'no',
            activeVendor: $this->developer['typo3']['vendor'] ?? 'all',
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
                    if (!($this->localExtensions[$extensionName] ?? false)) {

                        $extensionData['extension']['versionMajor'] = 0;
                        $extensionData['extension']['versionMinor'] = 1;
                        $extensionData['extension']['versionRevision'] = 0;

                        self::save(
                            $vendorName,
                            $extensionName,
                            $extensionData,
                        );

                        return $this->redirect('list', 'Extension');
                    } else {

                        if ($this->isComposerMode) {
 // ToDo LLL
                            $this->flashMessage('', 'Extension exists in typo3conf/ext, please change.');
						} else {
 // ToDo LLL
                            $this->flashMessage('', 'Extension exists in typo3conf/ext, please change.');
						}
                    }
                } else {
                    if ($vendorName) {
 // ToDo LLL
                        $this->flashMessage('', 'Please specify Extension name');
					} else {
 // ToDo LLL
                        $this->flashMessage('', 'Please specify Vendor name');
					}
			    }
                break;
		}
	
        if (!($extensionData ?? false)) {
            $extensionData = [];
        }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'extensionData' => $extensionData,
			'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            'projects' => $this->projects,
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

        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($extensionData,$bodyParams['extensionData']);

                self::save(
                    $bodyParams['vendorName'] ?? '',
                    $bodyParams['extensionName'] ?? '',
                    $extensionData ?? [],
                );

                return $this->redirect('list', 'Extension');
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
           'list',
           'Extension',
           'extension-edit-form',
        );

        return $this->moduleTemplate->renderResponse('Extension/Edit');
    }

    public function duplicateAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $this->moduleTemplate->renderResponse('Extension/Duplicate');
    }

    public function renameAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $this->moduleTemplate->renderResponse('Extension/Rename');
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $this->extensionbuilderObject->deleteExtension($vendorName, $extensionName);

        if ($this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? false) {
            unset($this->projects[$this->developer['typo3']['project']]['extensions'][$extensionName]);
            $this->writeProject();
            $this->readProject();
        }

        $this->flashMessage('', 'Extension: ' . $extensionName . ' is deleted'); // ToDo LLL

        return $this->redirect('list', 'Extension');
    }

    public function buildAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $builderUri = $this->configuration['builderUrl'];

        $copyInExtension = true;

        $flushT3andPhpCache = $this->developer['typo3']['flushT3andPhpCache'] ?? false;
        $analyzeDatabaseStructure = $this->developer['typo3']['analyzeDatabaseStructure'] ?? false;
        $rebuildPhpAutoload = $this->developer['typo3']['rebuildPhpAutoload'] ?? false;

        $this->extensionbuilderObject->build(
            $vendorName,
            $extensionName,
            $this->configuration,
            $this->developer,
        );

        $this->extensionbuilderObject->vendorsAndExtensions
            [$vendorName]['extensions'][$extensionName]['extensionBuild']['lastBuild'] = date('d-m-Y  h:i:m');
        $this->extensionbuilderObject->writeExtension($vendorName, $extensionName);

// Bei redirect falsh message!
//        return $this->redirect('list', 'Extension');

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Extension',
            activeProjcet: $this->developer['typo3']['project'] ?? 'no',
            activeVendor: $this->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            'add',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Extension/List');
    }

    public function uploadAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $this->addDocHeaderModuleDropDown(
            'Extension',
        );

        $vendorData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

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

        $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;

        $this->extensionbuilderObject->writeExtension($vendorName, $extensionName);

        $this->flashMessage('', 'Saving extension: ' . $extensionName); // ToDo LLL
    }

}