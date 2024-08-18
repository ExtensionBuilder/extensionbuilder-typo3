<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

class DeveloperModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
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

    final function developer(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
debug($bodyParams,'DeveloperModuleController.php - bodyParams - ToDo');

        $view = $this->moduleTemplateFactory->create($request);

		if (in_array($bodyParams['CMD'] ?? [], ['save',], true)) {
            $this->developer = $bodyParams['developerData'];
            $this->writeDeveloper();
            $this->flashMessage('', 'Saving developer setings');
        }

        $projects = [];
        $projects['no'] = 'No';
	    foreach ($this->projects ?? [] as $projectName => $projectData) {
            $projects[$projectName] = $projectData['projectName'];
	    }

        $vendors = [];
        $vendors['all'] = 'All';
	    foreach ($this->vendors ?? [] as $vendorName => $vendorData) {
            $vendors[$vendorName] = $vendorData['vendorName'];
	    }

        $view->assignMultiple([
            'developerData' => $this->developer,
            'projects' => $projects,
            'vendors' => $vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'developer'
        );

        $this->addDocHeaderCloseandSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );

    	return $view->renderResponse('Developer');
    }

}