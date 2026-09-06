<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Backend\Attribute\AsController;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
#[AsController]
final class ProjectController extends ExtensionBuilderController
{
    /**
     * @since 0.12
     */
    public function listAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        return $this->projectList();
    }

    /**
     * @since 0.12
     */
    private function projectList(): ResponseInterface
    {
        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'projects' => $this->ebBackendService->projects,
            'projectDev' => $this->ebBackendService->projectConfiguration,
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

    /**
     * @since 0.12
     */
    final public function addAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':

                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->projectConfiguration['fieldsEdit'],
                    $bodyParams['project'],
                );

                $project = $bodyParams['project'];

                if ($project['name']) {
                    $projectKey = uniqid();

                    if (!($this->ebBackendService->projects[$projectKey] ?? false)) {
                        $this->ebBackendService->projects[$projectKey] = $project;
                        $this->ebBackendService->writeProjects();

                        $this->flashMessage(
                            '',
                            $this->getTranslatedLabel(
                                $this->request,
                                $this->ebBackendService->lll . '.project.xlf:addProject',
                            ),
                        );

                        return $this->projectList();
                    }
                    $this->flashMessage('', 'project name exists please change'); // ToDo LLL

                } else {
                    $this->flashMessage('', 'Please specify project name'); // ToDo LLL
                }
                break;
        }

        $project = [];
        $project['name'] = '';
        $project['description'] = '';
        $project['extensions'] = [];

        $projectSelects = [];
        $projectSelects['vendors'] = $this->ebBackendService->getVendors();

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'projectData' => $project,
            'projectConfiguration' => $this->ebBackendService->projectConfiguration,
            'project' => $project,
            'projectSelects' => $projectSelects,
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

    /**
     * @since 0.12
     */
    final public function editAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->projectConfiguration['fieldsEdit'],
                    $bodyParams['project'],
                );

                $project = $bodyParams['project'];

                Tools\ConfigArray::arrayMerge($this->ebBackendService->projects[$projectKey], $project);

                $this->ebBackendService->writeProjects();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.project.xlf:projectSaved',
                    ),
                );
                break;
            case 'extensionOn':
                $this->ebBackendService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;
                $this->ebBackendService->writeProjects();
                $this->ebBackendService->readProjects();
                break;
            case 'extensionOff':
                $this->ebBackendService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = false;
                $this->ebBackendService->writeProjects();
                $this->ebBackendService->readProjects();
                break;
        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'projectData' => $this->ebBackendService->projects[$projectKey],
            'projectConfiguration' => $this->ebBackendService->projectConfiguration,
            'project' => $this->ebBackendService->projects[$projectKey],
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

    /**
     * @since 0.12
     */
    final public function deleteAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        if ($this->ebBackendService->projects[$projectKey] ?? false) {
            $flashMessage = 'Delete project ' . $this->ebBackendService->projects[$projectKey]['name']; // ToDo LLL
            $flashMessageInfo = '';
            unset($this->ebBackendService->projects[$projectKey]);
            $this->ebBackendService->writeProjects();
            $this->flashMessage($flashMessageInfo, $flashMessage); // ToDo LLL
        }

        return $this->projectList();
    }

    /**
     * @since 0.12
     */
    final public function addExtensionAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        $projectKey = $bodyParams['projectKey'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->ebBackendService->projects[$projectKey]['extensions'][$bodyParams['extensionName']] = [];
                $this->ebBackendService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;

                $this->ebBackendService->writeProjects();
                $this->ebBackendService->readProjects();

                break;
        }

        $project = [];
        $project['name'] = '';
        $project['description'] = '';
        $project['extensions'] = [];

        $extensions = [];
        foreach ($this->ebBackendService->vendorsAndExtensions ?? [] as $vendorKey => $vendorData) {
            foreach ($vendorData['extensions'] ?? [] as $extensionKey => $extensionData) {
                $extensions[$extensionData['extension']['extensionNamespace']] = $extensionData['extension'];
            }
        }
        foreach ($this->ebBackendService->projects[$projectKey]['extensions'] ?? [] as $extensionKey => $extensionData) {
            unset($extensions[$extensionKey]);
        }
        foreach ($this->ebBackendService->projects[$projectKey]['dependencies'] ?? [] as $dependencieKey => $dependencieData) {
            unset($extensions[$dependencieKey]);
        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
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

    /**
     * @since 0.12
     */
    final public function deleteExtensionAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        $projectKey = $bodyParams['projectKey'];

        unset($this->ebBackendService->projects[$projectKey]['extensions'][$bodyParams['extensionName']]);

        $this->ebBackendService->writeProjects();

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'projectData' => $this->ebBackendService->projects[$projectKey],
            'projectConfiguration' => $this->ebBackendService->projectConfiguration,
            'project' => $this->ebBackendService->projects[$projectKey],
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