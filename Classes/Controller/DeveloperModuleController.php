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
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

final class DeveloperModuleController extends BuildExtensionAbstract
{

// ToDo
// Flush TYPO3 and PHP Cache
// Analyze Database Structure
// Rebuild PHP Autoload Information

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

    final function developer(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $view = $this->moduleTemplateFactory->create($request);

        switch ($bodyParams['action'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($this->developer, $bodyParams['developer']);

                $this->writeDeveloper();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.developer.xlf:savingDeveloperSetings',
                    )
                );
                break;
		}

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
            'vendors' => $vendors,
            'projects' => $projects,
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