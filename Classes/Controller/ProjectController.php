<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ProjectController extends ExtensionBuilderController
{

    public function listAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $this->projectList();
    }

    private function projectList(): ResponseInterface {
        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'projects' => $this->ebService->projects,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderAddButton(
            'add',
            'Project',
        );

        return $this->moduleTemplate->renderResponse('Project/List');
	}

    final function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $project = $bodyParams['project'];

                if ($project['name']) {
                    $projectKey = uniqid();

                    if (!($this->ebService->projects[$projectKey] ?? false)) {
                        $this->ebService->projects[$projectKey] = $project;
                        $this->ebService->writeProject();

                        $this->flashMessage(
                            '',
                            $this->getTranslatedLabel(
                                $this->request,
                                $this->ebService->lll . '.project.xlf:addProject',
                            ),
                        );

                        return $this->projectList();
                    } else {
                        $this->flashMessage('', 'project name exists please change'); // ToDo LLL
				    }
                } else {
                    $this->flashMessage('', 'Please specify project name'); // ToDo LLL
			    }
                break;
		}

        $project = [];
        $project['name'] = '';
        $project['description'] = '';
        $project['extensions'] = [];

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'project' => $project,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Project',
        );
        $this->addDocHeaderSaveButton(
            'project-add-form',
            'Project',
        );

        return $this->moduleTemplate->renderResponse('Project/Add');
    }

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $project = $bodyParams['project'];

                Tools\ConfigArray::arrayMerge($this->ebService->projects[$projectKey], $project);

                $this->ebService->writeProject();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.project.xlf:projectSaved',
                    ),
                );
                break;

            case 'extensionOn':
                $this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;
                $this->ebService->writeProject();
                $this->ebService->readProject();
                break;

            case 'extensionOff':
                $this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = false;
                $this->ebService->writeProject();
                $this->ebService->readProject();
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'project' => $this->ebService->projects[$projectKey],
            'projectKey' => $projectKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Project',
        );
        $this->addDocHeaderSaveButton(
            'project-edit-form',
            'Project',
        );

    	return $this->moduleTemplate->renderResponse('Project/Edit');
    }

    final function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        if ($this->ebService->projects[$projectKey] ?? false) {
            unset($this->ebService->projects[$projectKey]);
            $this->ebService->writeProject();
            $this->flashMessage('', 'Delete'); // ToDo LLL
        }

        return $this->projectList();
    }

    final function addextensionAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']] = [];
                $this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;
                $this->ebService->writeProject();
                $this->ebService->readProject();

                break;
		}

        $project = [];
        $project['name'] = '';
        $project['description'] = '';
        $project['extensions'] = [];

		$extensions = [];
		foreach($this->ebService->vendorsAndExtensions ?? [] as $vendorKey => $vendorData) {
		    foreach($vendorData['extensions'] ?? [] as $extensionKey => $extensionData) {
		        $extensions[$extensionData['extension']['extensionName']] = $extensionData['extension'];
		    }
		}
		foreach($this->ebService->projects[$projectKey]['extensions'] ?? [] as $extensionKey => $extensionData) {
            unset($extensions[$extensionKey]);
		}
		foreach($this->ebService->projects[$projectKey]['dependencies'] ?? [] as $dependencieKey => $dependencieData) {
            unset($extensions[$dependencieKey]);
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'projectKey' => $projectKey,
            'extensions'  => $extensions,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButton(
            'edit',
            'Project',
             projectKey: $projectKey,
        );

        return $this->moduleTemplate->renderResponse('Project/AddExtesion');
    }

    final function deleteextensionAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'];

        unset($this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]);

        $this->ebService->writeProject();

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'project' => $this->ebService->projects[$projectKey],
            'projectKey' => $projectKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Project',
        );
        $this->addDocHeaderSaveButton(
            'project-add-form',
            'Project',
        );

        return $this->moduleTemplate->renderResponse('Project/Edit');
    }

}