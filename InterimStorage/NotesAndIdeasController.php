<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class NotesAndIdeasController extends ExtensionBuilderController
{

    public function listAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'notesAndIdeas' => $this->NotesAndIdeas ?? [],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Notesandideas',
        );
        $this->addDocHeaderAddButton(
            'add',
            'Notesandideas',
        );

        return $this->moduleTemplate->renderResponse('NotesAndIdeas/List');
    }






    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['action'] ?? '') {
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
                            $vendorName ?? '',
                            $extensionName ?? '',
                            $extensionData,
                        );

                        if ($bodyParams['extensionData']['project'] ?? false) {
                            $projectKey = $bodyParams['extensionData']['project'];
                            $this->projects[$projectKey]['extensions'][$extensionName] = [];
                            $this->projects[$projectKey]['extensions'][$extensionName]['extensionOnOff'] = true;
                            $this->projects[$projectKey]['extensions'][$extensionName]['extension'] = $extensionData['extension'];
                            $this->writeProject();
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
                            $this->developer['typo3']['project'] ?? 'no',
                            $this->developer['typo3']['vendor'] ?? 'all',
                        );
                        $this->addDocHeaderAddButton(
                            'add',
                            'Extension',
                        );

                        return $view->renderResponse('Extension/List');

                    } else {
// extensionbuilder_administration
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
        );

    	return $this->moduleTemplate->renderResponse('NotesAndIdeas/Add');
    }


    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];

        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['action'] ?? '') {
            case 'save':

if ($extensionName ===$bodyParams['extensionData']['extension']['extensionName']) {
} else {
//echo 'ToDo: change';
}

                Tools\ConfigArray::arrayMerge($extensionData,$bodyParams['extensionData']);

                self::save(
                    $bodyParams['vendorName'] ?? '',
                    $bodyParams['extensionName'] ?? '',
                    $extensionData ?? [],
                );

                $this->moduleTemplate->assignMultiple([
                    'configuration' => $this->configuration,
                    'currentProject' => $this->developer['typo3']['project'] ?? 'no',
                    'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
                    'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
                    'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
                ]);

                $this->addDocHeaderModuleDropDown(
                    'Extension',
                    $this->developer['typo3']['project'] ?? 'no',
                    $this->developer['typo3']['vendor'] ?? 'all',
                );
                $this->addDocHeaderAddButton(
                    'add',
                    'Extension',
                );

                return $this->moduleTemplate->renderResponse('NotesAndIdeas/List');
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
        );

        return $this->moduleTemplate->renderResponse('NotesAndIdeasModule/Edit');
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

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Extension',
            $this->developer['typo3']['project'] ?? 'no',
            $this->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            'add',
			'Extension',
        );

        return $this->moduleTemplate->renderResponse('Extension/List');
    }

}