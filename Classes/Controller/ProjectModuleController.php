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
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

final class ProjectModuleController extends BuildExtensionAbstract
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

    public function list(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'projects' => $this->projects,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
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
            'locallang.project.xlf:add',
			'project.add',
        );

        return $view->renderResponse('Project/List');
    }

    final function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        switch ($bodyParams['action'] ?? '') {
            case 'save':
                $project = $bodyParams['project'];

                if ($project['name']) {
                    $projectKey = uniqid();

                    if (!($this->projects[$projectKey] ?? false)) {

                        $this->projects[$projectKey] = $project;
                        $this->writeProject();

                        $this->flashMessage(
                            '',
                            $this->getTranslatedLabel(
                                $request,
                                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.project.xlf:addProject',
                            ),
                        );

                        $view->assignMultiple([
                            'configuration' => $this->configuration,
                            'projects' => $this->projects,
                        ]);

                        $this->addDocHeaderModuleDropDown(
                            $view,
                            $this->uriBuilder,
                            'project',
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
                            'locallang.project.xlf:add',
                            'project.add',
                        );

                        return $view->renderResponse('Project/List');
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

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'project' => $project,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'project',
        );

        return $view->renderResponse('Project/Add');
    }

    final function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        switch ($bodyParams['action'] ?? '') {
            case 'save':
                $project = $bodyParams['project'];

                Tools\ConfigArray::arrayMerge($this->projects[$projectKey], $project);

                $this->writeProject();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.project.xlf:projectSaved',
                    ),
                );

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'projects' => $this->projects,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'project'
                );
                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'project',
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
                   'locallang.project.xlf:add',
                   'project.add',
                );

                return $view->renderResponse('Project/List');
                break;
            case 'extensionOn':
                $this->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;
                $this->writeProject();
                $this->readProject();
                break;
            case 'extensionOff':
                $this->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = false;
                $this->writeProject();
                $this->readProject();
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'projectKey' => $projectKey,
            'project' => $this->projects[$projectKey],
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'project'
        );

    	return $view->renderResponse('Project/Edit');
    }

    final function duplicate(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        switch ($bodyParams['CMD'] ?? '') {
            case 'save':
                $project = $bodyParams['project'];

                if ($project['name']) {
                    $projectKeyNew = uniqid();

                    $this->duplicateProjects($projectKey, $projectKeyNew, $project['name']);

                    $view->assignMultiple([
                       'configuration' => $this->configuration,
                       'projects' => $this->projects,
                    ]);

                    $this->addDocHeaderModuleDropDown(
                        $view,
                        $this->uriBuilder,
                        'project',
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
                        'locallang.project.xlf:add',
                        'project.add',
                    );

                    return $view->renderResponse('Project/List');
                } else {
                    $this->flashMessage('', 'Please specify project name for duplicate'); // ToDo LLL
                }
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'project' => $this->projects[$projectKey],
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'project',
        );
        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );

        return $view->renderResponse('Project/Duplicate');
    }

    final function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $projectKey = $bodyParams['projectKey'] ?? 'noKey';

        if ($this->projects[$projectKey] ?? false) {
            unset($this->projects[$projectKey]);
            $this->writeProject();
            $this->flashMessage('', 'Delete'); // ToDo LLL
        }

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'projects' => $this->projects,
        ]);

        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.project.xlf:add',
			'project.add',
        );
        $this->addDocHeaderCloseButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );
        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );		

        return $view->renderResponse('Project/List');
    }

    final function addExtension(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $projectKey = $bodyParams['projectKey'];

        switch ($bodyParams['action'] ?? '') {
            case 'extensionAdd':
                $this->projects[$projectKey]['extensions'][$bodyParams['extensionName']] = [];
                $this->projects[$projectKey]['extensions'][$bodyParams['extensionName']]['extensionOnOff'] = true;
                $this->writeProject();
                $this->readProject();

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'project' => $this->projects[$projectKey],
                    'projectKey' => $projectKey,
                ]);

                $this->addDocHeaderModuleDropDown(
                    $view,
                    $this->uriBuilder,
                    'project',
                );
                $this->addDocHeaderCloseAndSaveButtons(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'project'
                );

                return $view->renderResponse('Project/Edit');
                break;
		}

        $project = [];
        $project['name'] = '';
        $project['description'] = '';
        $project['extensions'] = [];

		$extensions = [];
		foreach($this->vendorsAndExtensions ?? [] as $vendorKey => $vendorData) {
		    foreach($vendorData['extensions'] ?? [] as $extensionKey => $extensionData) {
		        $extensions[$extensionData['extension']['extensionName']] = $extensionData['extension'];
		    }
		}
		foreach($this->projects[$projectKey]['extensions'] ?? [] as $extensionKey => $extensionData) {
            unset($extensions[$extensionKey]);
		}
		foreach($this->projects[$projectKey]['dependencies'] ?? [] as $dependencieKey => $dependencieData) {

            unset($extensions[$dependencieKey]);
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'projectKey' => $projectKey,
            'extensions'  => $extensions,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );
        $this->addDocHeaderCloseButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'project.edit',
            projectKey: $projectKey,
        );

        return $view->renderResponse('Project/AddExtesion');
    }

    final function deleteExtension(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $projectKey = $bodyParams['projectKey'];

        unset($this->projects[$projectKey]['extensions'][$bodyParams['extensionName']]);

        $this->writeProject();
        $this->readProject();

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'project' => $this->projects[$projectKey],
            'projectKey' => $projectKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'project'
        );

        return $view->renderResponse('Project/Edit');
    }

}