<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Extbase\Http\ForwardResponse;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;

use TYPO3\CMS\Core\Core\Environment;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

final class VendorModuleController extends BuildExtensionAbstract
{

    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconRegistry $iconRegistry,
        protected readonly IconFactory $iconFactory,
        protected readonly Context $context,
    ) {
        parent::__construct();
    }


    final function list(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorList' => $this->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );
        if (($this->vendors ?? false) && !($this->extensionbuilderObject->vendorsAndExtensions ?? false)) {
            $this->addDocHeaderCloseButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension',
            );
        }
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.vendor.xlf:add',
            'vendor.add',
        );

        if (!($this->vendors['ExampleVendor'] ?? false)) { // ToDo ein und ausschalten über config
            $this->addDocHeaderImportExampleVendor(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.vendor.xlf:importExampleVendor',
                'vendor.importExampleVendor',
            );
		}

        return $view->renderResponse('Vendor/List');
    }


    final function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        switch ($bodyParams['action'] ?? '') {
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
                                $request,
                                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:savingVendor'
                            ) . $vendorName,
                        );

                        $view->assignMultiple([
                            'configuration' => $this->configuration,
                            'vendorList' => $this->vendors,
                        ]);

                        $this->addDocHeaderModuleDropDown(
                            $view,
                            $this->uriBuilder,
                            'vendor',
                        );
                        $this->addDocHeaderCloseButtons(
                            $view,
                            $this->iconFactory,
                            $this->uriBuilder,
                            'extension',
                        );
                        $this->addDocHeaderAddButton(
                            $view,
                            $this->iconFactory,
                            $this->uriBuilder,
                            'locallang.vendor.xlf:add',
	    		            'vendor.add',
                        );

                        return $view->renderResponse('Vendor/List');

                    } else {
                        $this->flashMessage('', 'vendor name exists please change'); // ToDo LLL
                    }
                } else {
                    $this->flashMessage('', 'Please specify vendor name'); // ToDo LLL
			    }

                break;
		}

        if (!($vendorData ?? false)) {
            $vendorData = [];
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
             $this->uriBuilder,
            'vendor',
        );

        return $view->renderResponse('Vendor/Add');
    }


    final function importExampleVendor(
        ServerRequestInterface $request,
    ): ResponseInterface {

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

// ToDo reditect
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorList' => $this->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );
        $this->addDocHeaderCloseButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.vendor.xlf:add',
            'vendor.add',
        );

//debug($request);

// ForwardResponse->withControllerName(string $controllerName): self


        return $view->renderResponse('Vendor/List');

	}


    final function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $vendorData = $this->vendors[$vendorName];

        switch ($bodyParams['action'] ?? '') {
            case 'save':

                Tools\ConfigArray::arrayMerge($vendorData, $bodyParams['vendorData']);

                $this->vendors[$vendorName] = $vendorData;

                $this->writeVendor($vendorName);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:savingVendor'
                    ) . $vendorName,
                );

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorList' => $this->vendors,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'vendor',
                );
                $this->addDocHeaderCloseButtons(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'extension',
                );
                $this->addDocHeaderAddButton(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'locallang.vendor.xlf:add',
                    'vendor.add',
                );

                return $view->renderResponse('Vendor/List');
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'vendor',
        );

    	return $view->renderResponse('Vendor/Edit');
    }


    final function duplicate(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $vendorNameOrg = $bodyParams['vendorName'];
        $vendorData = $this->vendors[$vendorNameOrg];

        switch ($bodyParams['action'] ?? '') {
            case 'save':
                $vendorNameNew = $bodyParams['vendorData']['vendorName'];

                $this->vendors[$vendorNameNew] = $this->vendors[$vendorNameOrg];
                $this->vendors[$vendorNameNew]['vendorName'] = $vendorNameNew;

                $this->writeVendor($vendorNameNew);

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:duplicateVendor'
                    ) . $bodyParams['vendorData']['vendorName'],
                );

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorList' => $this->vendors,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'vendor',
                );
                $this->addDocHeaderCloseButtons(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'extension',
                );
                $this->addDocHeaderAddButton(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'locallang.vendor.xlf:add',
                    'vendor.add',
                );

                return $view->renderResponse('Vendor/List');
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'vendor',
        );

    	return $view->renderResponse('Vendor/Duplicate');
    }


    final function rename(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'] ?? '';

        $vendorData = $this->vendors[$vendorName];

        switch ($bodyParams['action'] ?? '') {
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

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorList' => $this->vendors,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'vendor',
                );
                $this->addDocHeaderCloseButtons(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'extension',
                );
                $this->addDocHeaderAddButton(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'locallang.vendor.xlf:add',
                    'vendor.add',
                );

                return $view->renderResponse('Vendor/List');
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'vendor',
        );

    	return $view->renderResponse('Vendor/Rename');
    }

    final function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

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

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorList' => $this->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );
        $this->addDocHeaderCloseButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.vendor.xlf:add',
		    'vendor.add',
        );
        if (!($this->vendors['ExampleVendor'] ?? false)) { // ToDo ein und ausschalten über config
            $this->addDocHeaderImportExampleVendor(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.vendor.xlf:importExampleVendor',
                'vendor.importExampleVendor',
            );
		}

    	return $view->renderResponse('Vendor/List');
    }

}