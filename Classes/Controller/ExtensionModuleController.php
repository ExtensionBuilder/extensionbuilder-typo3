<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Reports\RequestAwareReportInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Registry;


// Syslog
//use TYPO3\CMS\Core\SysLog\Action\Database as SystemLogDatabaseAction;
//use TYPO3\CMS\Core\SysLog\Error as SystemLogErrorClassification;
//use TYPO3\CMS\Core\SysLog\Type as SystemLogType;


use ExtensionBuilder\ExtensionbuilderTypo3\Enumeration\Action;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;
use ExtensionBuilder\ExtensionbuilderTypo3\Utility\ModuleController;
use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;
use TYPO3\CMS\Core\Page\PageRenderer;

use ExtensionBuilder\ExtensionbuilderTypo3\Utility\Github;

class ExtensionModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
{

    public \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension $extensionbulderObject;

    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconRegistry $iconRegistry,
        protected readonly IconFactory $iconFactory,
        protected readonly Context $context,
        protected readonly PageRenderer $pageRenderer,
    ) {
        parent::__construct();

        $this->extensionbulderObject = new \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;
    }

    // Default Module
    public function list(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - list');

        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
//debug($bodyParams, "ExtensionModuleController.php");

		$view = $this->moduleTemplateFactory->create($request);

		if (in_array($bodyParams['CMD'] ?? [], ['save',], true)) {
            if ($this->noDeveloper) {
                $this->writeDeveloper();
                $this->flashMessage('', 'Saving developer configuration');
		    }
        }

		$this->pageRenderer->loadJavaScriptModule('@extensionbuilder/test.js');

//        $view->assign(
//            'dateFormat',
//            [
//                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
//                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
//            ]
//        );

        $view->setTitle(
            $GLOBALS['LANG']->sL(
                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab' ),
            $GLOBALS['LANG']->sL(
                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.extension' ),
        );

        if ($this->noDeveloper) {
            $view->assignMultiple([
                'developer' => $this->developer,
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
                'developer',
            );

            return $view->renderResponse('Developer');
		}

        if (!$this->extensionbulderObject->vendorsAndExtensions) {
            $view->assignMultiple(
                ['vendorList' => $this->vendorsAndExtensions]
            );

            $this->addDocHeaderModuleDropDown(
                $view,
                $this->uriBuilder,
                'vendor',
            );
            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.vendor.add.h',
                'vendor.add',
                );

            return $view->renderResponse('VendorList');
        }

        $view->assignMultiple([
            'vendors' => $this->extensionbulderObject->vendorsAndExtensions ?? ['No data! - renderExtensionListView'],
            'currentProjects' => $this->extensionbulderObject->currentProjects ?? ['No data! - renderExtensionListView'],
            'projects' => $this->extensionbulderObject->projects ?? ['No data! - renderExtensionListView'],
        ]);

// Test JS
//        ModuleController::addDocHeaderAddButton(
//            $view,
//            $this->iconFactory,
//            $this->uriBuilder,
//            'locallang.xlf:function.extension.add.h',
//			'extension.test',
//        );

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.extension.add.h',
			'extension.add',
        );

        return $view->renderResponse('ExtensionList');
    }

    public function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Add');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

		$view = $this->moduleTemplateFactory->create($request);

//        $view->assign(
//            'dateFormat',
//            [
//                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
//                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
//            ]
//        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        if (in_array($bodyParams['CMD'] ?? [], ['save',], true)) {

            $vendorName = $bodyParams['extensionData']['extension']['vendorName'];
            $extensionName = $bodyParams['extensionData']['extension']['extensionName'];


//                $tmpNode = 'extension';
//    		    $tmpName = $tableName;

//        		$tmpData = [];
//        		$tmpData[$tmpNode] = [];
//        		$tmpData[$tmpNode][$tmpName]['makeSql'] = true;
//        		$tmpData[$tmpNode][$tmpName]['makeModel'] = true;
//    		    $tmpData[$tmpNode][$tmpName]['makeTca'] = true;
//    	    	$tmpData[$tmpNode][$tmpName]['makeFluid'] = true;
//        		$tmpData[$tmpNode][$tmpName]['language'] = [];
//        		$tmpData[$tmpNode][$tmpName]['language']['en'] = $tableNameUc;
//                $tmpData[$tmpNode][$tmpName] = $parsedBody['tableData'] ?? [];

//Tools\ConfigArray::arrayMerge($extensionData,$tmpData);
            self::save(
                $vendorName ?? '',
                $extensionName ?? '',
                $bodyParams['extensionData'] ?? [],
            );

            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.extension.add.h',
			    'extension.add',
            );

            $view->assignMultiple([
                'vendors' => $this->extensionbulderObject->vendorsAndExtensions ?? ['No data! - renderExtensionListView'],
                'currentProjects' => $this->extensionbulderObject->currentProjects ?? ['No data! - renderExtensionListView'],
                'projects' => $this->extensionbulderObject->projects ?? ['No data! - renderExtensionListView'],
            ]);

            return $view->renderResponse('ExtensionList');
        }

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );

		$extensionData = [];
		$extensionData['extension'] = [];
		$extensionData['extension']['vewndorName'] = '';
		$extensionData['extension']['extensionName'] = '';
		$extensionData['extension']['description'] = '';

        $view->assignMultiple([
            'extensionData' => $extensionData,
			'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);

    	return $view->renderResponse('ExtensionAdd');
    }


    public function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Edit');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

debug($bodyParams,"bodyParams");

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

//        $view->assign(
//            'dateFormat',
//            [
//                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
//                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
//            ]
//        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        if (in_array($bodyParams['CMD'] ?? [], ['save',], true)) {

if ($extensionName ===$bodyParams['extensionData']['extension']['extensionName']) {
} else {
//echo 'ToDo: change';
}

            Tools\ConfigArray::arrayMerge($extensionData,$bodyParams['extensionData']);

            self::save(
                $bodyParams['vendorName'] ?? '',
                $bodyParams['extensionName'] ?? '',
                $extensionData ?? [],
            );

            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.extension.add.h',
			    'extension.add',
            );

            $view->assignMultiple([
                'vendors' => $this->extensionbulderObject->vendorsAndExtensions ?? ['No data! - renderExtensionListView'],
                'currentProjects' => $this->extensionbulderObject->currentProjects ?? ['No data! - renderExtensionListView'],
                'projects' => $this->extensionbulderObject->projects ?? ['No data! - renderExtensionListView'],
            ]);

            return $view->renderResponse('ExtensionList');
        } else {

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'extensionData' => $extensionData,
		    	"registeredVendorGroups" => $this->getRegisteredVendorGroups(),
            ]);

            $this->addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension',
            );

    	    return $view->renderResponse('ExtensionEdit');
		}
    }


    public function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Delete');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

//        $view->assign(
//            'dateFormat',
//            [
//                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
//                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
//            ]
//        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        $this->extensionbulderObject->delete($vendorName, $extensionName);

        $view->assignMultiple([
            'vendors' => $this->extensionbulderObject->vendorsAndExtensions ?? ['No data! - renderExtensionListView'],
            'currentProjects' => $this->extensionbulderObject->currentProjects ?? ['No data! - renderExtensionListView'],
            'projects' => $this->extensionbulderObject->projects ?? ['No data! - renderExtensionListView'],
        ]);

        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.extension.add.h',
			'extension.add',
        );

        return $view->renderResponse('ExtensionList');
    }


    public function build(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Build');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

//        $view->assign(
//            'dateFormat',
//            [
//                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
//                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
//            ]
//        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        $builderUri = 'https://build.extension-builder.com/';
        $copyInExtension = true;
        $clearCache      = false;
        $dumpAutoload    = false;

        $this->extensionbulderObject->build(
            $vendorName,
            $extensionName,
            $builderUri,
            $copyInExtension,
            $clearCache,
            $dumpAutoload,
        );
        $this->extensionbulderObject->vendorsAndExtensions
            [$vendorName]['extensions'][$extensionName]['extensionBuild']['lastBuild'] = date('d-m-Y  h:i:m');
        $this->extensionbulderObject->write($vendorName, $extensionName);

        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.extension.add.h',
			'extension.add',
        );

        $view->assignMultiple([
            'vendors' => $this->extensionbulderObject->vendorsAndExtensions ?? ['No data! - renderExtensionListView'],
            'currentProjects' => $this->extensionbulderObject->currentProjects ?? ['No data! - renderExtensionListView'],
            'projects' => $this->extensionbulderObject->projects ?? ['No data! - renderExtensionListView'],
        ]);

        return $view->renderResponse('ExtensionList');
    }


    public function upload(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Upload');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $extensionName = $bodyParams['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

//        $view->assign(
//            'dateFormat',
//            [
//                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
//                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
//            ]
//        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        $vendorData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName];
        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

//echo 'ExtensionModuleController.php - upload<br />';

//debug($vendorData);
//debug($vendorData['github']);
//debug($extensionData['gitubCom']);
//debug($extensionData['extensionBuild']['gitubCom']);

        // GtiHub
        if ($extensionData['extensionBuild']['gitHubCom'] ?? false) {
            $tmpGitubCom = true;
            $gitOrganizations = $extensionData['extensionBuild']['gitHubCom']['vendor'] ?? '';
            $gitToken = $extensionData['extensionBuild']['gitHubCom']['token'] ?? '';
            $gitRepos = $extensionName;
            if (Github\Helpers::findRepos($gitOrganizations, $gitToken, $gitRepos)) {

//echo 'findRepos<br />';

            } else {

//echo 'createRepos<br />';
                Github\Helpers::createRepos($gitOrganizations, $gitToken, $gitRepos);
			}
        }

        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.extension.add.h',
			'extension.add',
        );

        $view->assignMultiple([
            'vendors' => $this->extensionbulderObject->vendorsAndExtensions ?? ['No data! - renderExtensionListView'],
            'currentProjects' => $this->extensionbulderObject->currentProjects ?? ['No data! - renderExtensionListView'],
            'projects' => $this->extensionbulderObject->projects ?? ['No data! - renderExtensionListView'],
        ]);

        return $view->renderResponse('ExtensionList');
    }


    // ------------------------------------------------------------------


    final function save(
        string $vendorName,
        string $extensionName,
        array $extensionData,
    ): void {

        $extensionData['extension']['version']  = (string)($extensionData['extension']['versionMajor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionMinor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionRevision'] ?? '0');

        $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;
        $this->extensionbulderObject->write($vendorName, $extensionName);

        ModuleController::flashMessage('Vendor: '.$vendorName, 'Saving extension: '.$extensionName);
    }


    final function getRegisteredVendorGroups(): array
    {
        $tmpArray = [];
        foreach ($this->extensionbulderObject->vendorsAndExtensions ?? [] as $vendor) {
            $tmpArray[] = $vendor['vendorName'];
        }
        return $tmpArray;
    }

}