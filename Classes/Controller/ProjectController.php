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
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'projects' => $this->ebService->projects,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButtons(
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
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
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

                        return $this->redirect('list', 'Project');
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
            'configuration' => $this->ebService->configuration,
            'project' => $project,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Project',
            'project-add-form',
        );

        return $this->moduleTemplate->renderResponse('Project/Add');
    }

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
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

                return $this->redirect('list', 'Project');
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
            'configuration' => $this->ebService->configuration,
            'project' => $this->ebService->projects[$projectKey],
            'projectKey' => $projectKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Project',
            'project-edit-form',
        );

    	return $this->moduleTemplate->renderResponse('Project/Edit');
    }

    final function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        if ($this->ebService->projects[$projectKey] ?? false) {
            unset($this->ebService->projects[$projectKey]);
            $this->ebService->writeProject();
            $this->flashMessage('', 'Delete'); // ToDo LLL
        }

        return $this->redirect('list', 'Project');
    }

    final function addextensionAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'extensionAdd':
                $this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']] = [];
                $this->ebService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;
                $this->ebService->writeProject();
                $this->ebService->readProject();

                $this->moduleTemplate->assignMultiple([
                    'configuration' => $this->ebService->configuration,
                    'project' => $this->ebService->projects[$projectKey],
                    'projectKey' => $projectKey,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $this->uriBuilder,
                    'Project',
                );
                $this->addDocHeaderCloseAndSaveButtons(
                    'project'
                );

                return $this->moduleTemplate->renderResponse('Project/Edit');
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
            'configuration' => $this->ebService->configuration,
            'projectKey' => $projectKey,
            'extensions'  => $extensions,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseButtons(
            'edit',
            'Project',
             projectKey: $projectKey,
        );

        return $this->moduleTemplate->renderResponse('Project/AddExtesion');
    }

    final function deleteextensionAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $projectKey = $bodyParams['projectKey'];

        unset($this->projects[$projectKey]['extensions'][$bodyParams['extensionName']]);

        $this->ebService->writeProject();
        $this->ebService->readProject();

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'project' => $this->ebService->projects[$projectKey],
            'projectKey' => $projectKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            'project'
        );

        return $this->moduleTemplate->renderResponse('Project/Edit');
    }

}