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
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

class ProjectModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
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
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);
        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );		
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.project.add.h',
			'project.add',
        );
        $view->assignMultiple([
            'projects' => $this->projects,
        ]);
        return $view->renderResponse('ProjectList');
    }
	

    final function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);
        if ( in_array( $bodyParams['CMD'] ?? [], ['save'], true ) ) {
            $project = $bodyParams['project'];
            if ($project['name']) {
                $projectName = lcfirst(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($project['projectName']));
                if (!($this->projects[$projectName] ?? false)) {
                    $this->projects[$projectName] = $project;
                    $this->writeProjects();
                    $view->assignMultiple([
                        'projects' => $this->projects,
                    ]);
                    $this->addDocHeaderModuleDropDown(
                        $view,
                        $this->uriBuilder,
                        'project',
                    );		
                    $this->addDocHeaderAddButton(
                        $view,
                        $this->iconFactory,
                        $this->uriBuilder,
                        'locallang.xlf:function.project.add.h',
            			'project.add',
                    );
                    return $view->renderResponse('ProjectList');
                } else {
                    $this->flashMessage('', 'project name exists please change');
				}
            } else {
                $this->flashMessage('', 'Please specify project name');
			}
		} else {
            $project = [];
            $project['name'] = '';
            $project['description'] = '';
            $project['extensions'] = [];
		}
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
        $view->assignMultiple([
            'project' => $project,
        ]);
        return $view->renderResponse('ProjectAdd');
    }



    final function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
debug($bodyParams);
		$view = $this->moduleTemplateFactory->create($request);

        if ( in_array( $bodyParams['CMD'] ?? [], ['save'], true ) ) {
            $project = $bodyParams['project'];

		}
		
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

// ToDO
//        $view->assignMultiple([
//            'project' => $project,
//        ]);

    	return $view->renderResponse('ProjectEdit');
    }


    final function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
debug($bodyParams, 'delete');


$projectName = lcfirst(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($bodyParams['projectName']));

if ($this->projects[$projectName] ?? false) {
    $this->flashMessage('', 'Delete');
}

		$view = $this->moduleTemplateFactory->create($request);
        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'project',
        );		
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.project.add.h',
			'project.add',
        );
        $view->assignMultiple([
            'projects' => $this->projects,
        ]);
        return $view->renderResponse('ProjectList');
    }
}