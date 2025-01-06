<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Reports\RequestAwareReportInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Registry;

use TYPO3\CMS\Core\Page\PageRenderer;

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;
use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;

final class ExtensionModuleController extends BuildExtensionAbstract
{

    public BuildExtension $extensionbuilderObject;

    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconRegistry $iconRegistry,
        protected readonly IconFactory $iconFactory,
        protected readonly Context $context,
        protected readonly PageRenderer $pageRenderer,
    ) {
        parent::__construct();

        $this->extensionbuilderObject = new BuildExtension;
    }

    public function list(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

$this->pageRenderer->addJsFile('EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Modal.js');

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

        if ($this->noDeveloper) {
            if ($bodyParams['action'] ?? false) {
                switch ($bodyParams['action'] ?? '') {
                    case 'save':
                        Tools\ConfigArray::arrayMerge($this->developer, $bodyParams['developer']);
                        $this->writeDeveloper();
                        $this->flashMessage(
                            '',
                            $this->getTranslatedLabel(
                                $request,
                                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.developer.xlf:savingDeveloperSetings',
                            ),
                        );
                        break;
		        }
            } else {
                $projects = [];
                $projects['no'] = $this->getTranslatedLabel(
                    $request,
                    'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.project.xlf:noProject',
                );
        	    foreach ($this->projects ?? [] as $projectName => $projectData) {
                    $projects[$projectName] = $projectData['name'];
    	        }

                $vendors = [];
                $vendors['all'] = $this->getTranslatedLabel(
                    $request,
                    'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:showAllVendors',
                );
                $vendors['no'] = $this->getTranslatedLabel(
                    $request,
                   'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:noVendors',
                );

        	    foreach ($this->vendors ?? [] as $vendorName => $vendorData) {
                    $vendors[$vendorName] = $vendorData['vendorName'];
    	        }

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'developer' => $this->developer,
                    'projects' => $projects,
                    'vendors' => $vendors,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'developer',
                );
                $this->addDocHeaderCloseAndSaveButtons(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'extension',
                );

                return $view->renderResponse('Developer');
			}
        }

        // No Vendor exists
        if (!($this->vendors)) {
            $view->assignMultiple([
                'configuration' => $this->configuration,
                'vendorList' => $this->vendors,
            ]);

            $this->addDocHeaderModuleDropDown(
                $view,
                $this->uriBuilder,
                'vendor',
            );
            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.vendor.xlf:add',
                'vendor.add',
            );

            return $view->renderResponse('Vendor/List');
        }

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
            activeProjcet: $this->developer['typo3']['project'] ?? 'no',
            activeVendor: $this->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.extension.xlf:add',
            'extension.add',
        );

        return $view->renderResponse('Extension/List');
    }

    public function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

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

                        $view->assignMultiple([
                            'configuration' => $this->configuration,
                            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
                            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
                            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
                            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
                        ]);

                        $this->addDocHeaderModuleDropDown(
                            $view,
                            $this->uriBuilder,
                            'extension',
                            $this->developer['typo3']['project'] ?? 'no',
                            $this->developer['typo3']['vendor'] ?? 'all',
                        );
                        $this->addDocHeaderAddButton(
                            $view,
                            $this->iconFactory,
                            $this->uriBuilder,
                            'locallang.extension.xlf:add',
                            'extension.add',
                        );

                        return $view->renderResponse('Extension/List');

                    } else {
debug($this);
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

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'extensionData' => $extensionData,
			'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            'projects' => $this->projects,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );

    	return $view->renderResponse('Extension/Add');
    }


    public function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

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

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'currentProject' => $this->developer['typo3']['project'] ?? 'no',
                    'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
                    'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
                    'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'extension',
                    $this->developer['typo3']['project'] ?? 'no',
                    $this->developer['typo3']['vendor'] ?? 'all',
                );
                $this->addDocHeaderAddButton(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'locallang.extension.xlf:add',
                    'extension.add',
                );

                return $view->renderResponse('Extension/List');
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            "registeredVendorGroups" => $this->getRegisteredVendorGroups(),
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
           'extension',
        );

        return $view->renderResponse('Extension/Edit');
    }

    public function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $this->extensionbuilderObject->deleteExtension($vendorName, $extensionName);

        if ($this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? false) {
            unset($this->projects[$this->developer['typo3']['project']]['extensions'][$extensionName]);
            $this->writeProject();
            $this->readProject();
        }

        $this->flashMessage('', 'Extension: ' . $extensionName . ' is deleted'); // ToDo LLL

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
            $this->developer['typo3']['project'] ?? 'no',
            $this->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.extension.xlf:add',
			'extension.add',
        );

        return $view->renderResponse('Extension/List');
    }

    public function build(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';


        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        $builderUri = Setup\Config::BUILDERURI;

        $copyInExtension = true;


// ToDo
// flushT3andPhpCache
// analyzeDatabaseStructure
// rebuildPhpAutoload

// https://typo3.extension-builder.dev/api/v1/extensionbuildcoretypo3
// if ($_SERVER['SERVER_NAME'] === 'development.extension-builder.dev') {


        $flushT3andPhpCache = $this->developer['typo3']['flushT3andPhpCache'] ?? false;
        $analyzeDatabaseStructure = $this->developer['typo3']['analyzeDatabaseStructure'] ?? false;
        $rebuildPhpAutoload = $this->developer['typo3']['rebuildPhpAutoload'] ?? false;

        $this->extensionbuilderObject->build(
            $vendorName,
            $extensionName,
            $builderUri,
            $copyInExtension,
            $flushT3andPhpCache,
            $analyzeDatabaseStructure,
            $rebuildPhpAutoload,
        );
        $this->extensionbuilderObject->vendorsAndExtensions
            [$vendorName]['extensions'][$extensionName]['extensionBuild']['lastBuild'] = date('d-m-Y  h:i:m');
        $this->extensionbuilderObject->writeExtension($vendorName, $extensionName);

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
            $this->developer['typo3']['project'] ?? 'no',
            $this->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.extension.xlf:add',
			'extension.add',
        );

        return $view->renderResponse('Extension/List');
    }


    public function upload(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        $vendorData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        // GtiHub
        if ($extensionData['extensionBuild']['gitHubCom'] ?? false) {

//debug($extensionData['extensionBuild']['gitHubCom'], $extensionName);
			
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

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'currentProject' => $this->developer['typo3']['project'] ?? 'no',
            'currentVendor' => $this->developer['typo3']['vendor'] ?? 'all',
            'project' =>  $this->projects[($this->developer['typo3']['project'] ?? 'no')] ?? [],
            'vendors' => $this->extensionbuilderObject->vendorsAndExtensions ?? ['no'],
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
            $this->developer['typo3']['project'] ?? 'no',
            $this->developer['typo3']['vendor'] ?? 'all',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.extension.xlf:add',
			'extension.add',
        );

        return $view->renderResponse('Extension/List');
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