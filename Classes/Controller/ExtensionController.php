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
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

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

//   $formData = [
//        'parameterArray' => [
//            'fieldConf' => [
//                'config' => [
//                    'type' => 'text',
//                    'enableRichtext' => true,
//                    'richtextConfiguration' => 'default',
//                    'fieldControl' => [
//                        'fullScreenRichtext' => [
//                            'disabled' => false,
//                        ],
//                    ],
//                ],
//            ],
//            'itemFormElName' => 'data[my_rte_field]',
//            'itemFormElValue' => '',
//        ],
//    ];

//       $rteHtml = $this->nodeFactory->create([
//            'type' => 'text',
//            'renderType' => 'textTable', // wichtig für RTE
//            'name' => 'data[my_rte_field]',
//            'data' => $formData,
//        ])->render()['html'];

//$this->view->assign('rteHtml', $rteHtml);

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
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'isProKey' => $this->isProKey,
            'components' =>  $this->ebService->extensionConfiguration['components'],
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
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
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
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'extensionData' => $extensionData,
            'projects' => $this->ebService->projects,
			'registeredVendorGroups' => $this->ebService->getRegisteredVendorGroups(),
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

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $componentsDev = $this->ebService->extensionConfiguration['components'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($extensionData, $bodyParams['extensionData']);

                self::save(
                    $vendorName ?? '',
                    $extensionName ?? '',
                    $extensionData ?? [],
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'componentsDev' =>  $componentsDev,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'registeredVendorGroups' => $this->ebService->getRegisteredVendorGroups(),
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

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
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
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

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
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'components' =>  $this->ebService->extensionConfiguration['components'],
            'registeredVendorGroups' => $this->ebService->getRegisteredVendorGroups(),
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
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

    public function listbuildAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
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
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'components' =>  $this->ebService->extensionConfiguration['components'],
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
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
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