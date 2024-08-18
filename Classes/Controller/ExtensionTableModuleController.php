<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;

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

use ExtensionBuilder\ExtensionbuilderTypo3\Enumeration\Action;

//use ExtensionBuilder\ExtensionbuilderTypo3\ManageExtension;
use ExtensionBuilder\ExtensionbuilderTypo3\Utility\ModuleController;
use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;


class ExtensionTableModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
{

    public \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension $extensionbulderObject;


    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconRegistry $iconRegistry,
        protected readonly IconFactory $iconFactory,
        protected readonly Context $context,
        private readonly PageRenderer $pageRenderer,
    ) {
        parent::__construct();

        $this->extensionbulderObject = new \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;

//        debug( $this->extensionbulderObject );
//        debug( $this->configuration, 'configuration' );

    }



    public function add(
        ServerRequestInterface $request
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'add table');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);
        $view->assign(
            'dateFormat',
            [
                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
            ]
        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        if (in_array($parsedBody['CMD'] ?? [], ['save',], true)) {

    		$extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

    		$tableName = strtolower($parsedBody['tableName']);

    		if ($tableName) {
        		$tableNameUc = ucfirst($tableName);

                $tmpNode = 'tables';
    		    $tmpName = $tableName;

        		$tmpData = [];
        		$tmpData[$tmpNode] = [];
        		$tmpData[$tmpNode][$tmpName] = [];
        		$tmpData[$tmpNode][$tmpName]['makeSql'] = true;
        		$tmpData[$tmpNode][$tmpName]['makeModel'] = true;
    		    $tmpData[$tmpNode][$tmpName]['makeTca'] = true;
    	    	$tmpData[$tmpNode][$tmpName]['makeFluid'] = true;
        		$tmpData[$tmpNode][$tmpName]['language'] = [];
        		$tmpData[$tmpNode][$tmpName]['language']['en'] = $tableNameUc;
                $tmpData[$tmpNode][$tmpName] = $parsedBody['tableData'] ?? [];

    		    Tools\ConfigArray::arrayMerge($extensionData,$tmpData);
                self::save(
                    $vendorName ?? '',
                    $extensionName ?? '',
                    $extensionData ?? [],
                );
    		}

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'extensionData' => $extensionData,
		    	'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            ModuleController::addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension.edit',
            );
			
            return $view->renderResponse('ExtensionEdit');
        } else {

            ModuleController::addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension.edit',
                $vendorName,
                $extensionName,
            );

		    $extensionData = [];
		    $extensionData['extension'] = [];
		    $extensionData['extension']['vewndorName'] = '';
		    $extensionData['extension']['extensionName'] = '';
		    $extensionData['extension']['description'] = '';

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
            ]);
        	return $view->renderResponse('ExtensionTableAdd');
        }
    }


    public function edit(
        ServerRequestInterface $request
    ): ResponseInterface {
        ModuleController::debugRequest($request, 'table edit');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';
        $tableName = $queryParams['tableName'];

		$view = $this->moduleTemplateFactory->create($request);
        $view->assign(
            'dateFormat',
            [
                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
            ]
        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );
//        ModuleController::addDocHeaderModuleDropDown(
//            $view,
//            $this->uriBuilder,
//            'extension'
//        );

        // Load JavaScript via PageRenderer
        $this->pageRenderer->loadJavaScriptModule('@extensionbuildercom/extensionbuilder_typo3/example.js');

        // Load JavaScript via JavaScriptRenderer
//        $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
//            JavaScriptModuleInstruction::create('@extensionbuildercom/extensionbuilder_typo3/example.js')
//        );

        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        if (in_array($parsedBody['CMD'] ?? [], ['save',], true)) {

            $tmpNode = 'tables';
    		$tmpName = $tableName;

    		$tmpData = [];
    		$tmpData[$tmpNode] = [];
    		$tmpData[$tmpNode][$tmpName] = [];
    		$tmpData[$tmpNode][$tmpName] = $parsedBody['tableData'] ?? [];
			
            Tools\ConfigArray::arrayMerge($extensionData,$tmpData);
	

            self::save(
                $parsedBody['vendorName'] ?? '',
                $parsedBody['extensionName'] ?? '',
                $extensionData ?? [],
            );
            ModuleController::addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.extension.add.h',
			    'extension.add',
            );
            $view->assignMultiple([
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'extensionData' => $extensionData,
                'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);
            return $view->renderResponse('ExtensionEdit');
        } else {

            $tableData = $extensionData['tables'][$tableName];

            ModuleController::addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension.edit',
                $vendorName,
                $extensionName,
            );
            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'tableName' => $tableName,
                'tableData' => $tableData,
            ]);			
            return $view->renderResponse('ExtensionTableEdit');
		}
    }


    public function delete(
        ServerRequestInterface $request
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'table delete');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';

        $tmpNode = 'tables';
        $tmpName = $request->getQueryParams()['tableName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);
        $view->assign(
            'dateFormat',
            [
                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
            ]
        );

//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );
//        ModuleController::addDocHeaderModuleDropDown(
//            $view,
//            $this->uriBuilder,
//            'extension'
//        );

        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

		unset( $extensionData[$tmpNode][$tmpName] );

        self::save(
            $vendorName ?? '',
            $extensionName ?? '',
            $extensionData ?? [],
        );

        $view->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);
        ModuleController::addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
            $vendorName,
            $extensionName
        );

//        ModuleController::addDocHeaderAddButton(
//            $view,
//            $this->iconFactory,
//            $this->uriBuilder,
//            'locallang.xlf:function.extension.add.h',
//			 'extension.add',
//        );

        return $view->renderResponse('ExtensionEdit');
    }


    // ------------------------------------------------------------------


    protected function save(
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
        $this->extensionbulderObject->write( $vendorName, $extensionName );

        ModuleController::flashMessage( 'Vendor: '.$vendorName, 'Saving extension: '.$extensionName );
    }


    protected function getRegisteredVendorGroups(): array
    {
        $tmpArray = [];
        foreach( $this->extensionbulderObject->vendorsAndExtensions ?? [] as $vendor ) {
            $tmpArray[] = $vendor['vendorName'];
        }
        return $tmpArray;
    }


    private function getLanguageService(ServerRequestInterface $request): LanguageService
    {
        return $this->languageServiceFactory->createFromSiteLanguage(
            $request->getAttribute('language')
            ?? $request->getAttribute('site')->getDefaultLanguage()
        );
    }

}