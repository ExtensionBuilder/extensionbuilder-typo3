<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class DeveloperController extends ExtensionBuilderController
{

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($this->developer, $bodyParams['developer']);

                $this->writeDeveloper();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.developer.xlf:savingDeveloperSetings',
                    )
                );
                break;
		}

        $projects = [];
        $projects['no'] = $this->getTranslatedLabel(
            $this->request,
            'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.project.xlf:noProject',
        );

	    foreach ($this->projects ?? [] as $projectName => $projectData) {
            $projects[$projectName] = $projectData['name'];
	    }

        $vendors = [];
        $vendors['all'] = $this->getTranslatedLabel(
            $this->request,
            'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:showAllVendors',
        );
        $vendors['no'] = $this->getTranslatedLabel(
            $this->request,
            'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.vendor.xlf:noVendors',
        );

	    foreach ($this->vendors ?? [] as $vendorName => $vendorData) {
            $vendors[$vendorName] = $vendorData['vendorName'];
	    }

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'developer' => $this->developer,
            'vendors' => $vendors,
            'projects' => $projects,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Developer',
        );

        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Extension',
            'developer-form',
        );

    	return $this->moduleTemplate->renderResponse('Developer');
    }

}