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
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($this->ebService->developer, $bodyParams['developer']);

                $this->ebService->writeDeveloper();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebService->lll . '.developer.xlf:savingDeveloperSetings',
                    )
                );
                break;
		}

        $projects = [];
        $projects['no'] = $this->getTranslatedLabel(
            $this->request,
            $this->ebService->lll . '.project.xlf:noProject',
        );

	    foreach ($this->ebService->projects ?? [] as $projectName => $projectData) {
            $projects[$projectName] = $projectData['name'];
	    }

        $vendors = [];
        $vendors['all'] = $this->getTranslatedLabel(
            $this->request,
            $this->ebService->lll . '.vendor.xlf:showAllVendors',
        );
        $vendors['no'] = $this->getTranslatedLabel(
            $this->request,
            $this->ebService->lll . '.vendor.xlf:noVendors',
        );

	    foreach ($this->ebService->vendors ?? [] as $vendorName => $vendorData) {
            $vendors[$vendorName] = $vendorData['vendorName'];
	    }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'isProKey' => $this->isProKey,
            'developer' => $this->ebService->developer,
            'vendors' => $vendors,
            'projects' => $projects,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Developer',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderSaveButton(
            'developer-form',
            'Develope',
        );

    	return $this->moduleTemplate->renderResponse('Developer');
    }

}