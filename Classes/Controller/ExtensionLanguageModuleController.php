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

use ExtensionBuilder\ExtensionbuilderTypo3\Enumeration\Action;

// use ExtensionBuilder\ExtensionbuilderTypo3\ManageExtension;
use ExtensionBuilder\ExtensionbuilderTypo3\Utility\ModuleController;
use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;




class ExtensionLanguageModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
{

    public \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension $extensionbulderObject;
    public array $configuration = [];
    public array $projects = [];

    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconRegistry $iconRegistry,
        protected readonly IconFactory $iconFactory,
        protected readonly Context $context,
    ) {
        parent::__construct();

        $this->extensionbulderObject = new \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;


//        debug( $this->extensionbulderObject );
//        debug( $this->configuration, 'configuration' );

    }



    public function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        ModuleController::debugRequest($request, 'Enumeration add');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

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

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        if ($parsedBody['extensionData'] ?? false) {
            $vendorName = $parsedBody['extensionData']['extension']['vendorName'];
            $extensionName = $parsedBody['extensionData']['extension']['extensionName'];
        } else {
            $vendorName = $request->getQueryParams()['vendorName'] ?? '';
            $extensionName = $request->getQueryParams()['extensionName'] ?? '';
		}
		
        if (in_array($parsedBody['CMD'] ?? [], ['save',], true)) {
    		$extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

    		$tableName = strtolower($parsedBody['tableName']);
    		$tableNameUc = ucfirst($tableName);

    		$tableData = [];
    		$tableData['tables'] = [];
    		$tableData['tables'][$tableName] = [];
    		$tableData['tables'][$tableName]['makeSql'] = true;
    		$tableData['tables'][$tableName]['makeModel'] = true;
    		$tableData['tables'][$tableName]['makeTca'] = true;
    		$tableData['tables'][$tableName]['makeFluid'] = true;
    		$tableData['tables'][$tableName]['language'] = [];
    		$tableData['tables'][$tableName]['language']['en'] = $tableNameUc;
    		if ($parsedBody['tableData']['description'] ?? false) {
    		    $tableData['tables'][$tableName]['description'] = $parsedBody['tableData']['description'];
    		}
    		Tools\ConfigArray::arrayMerge($extensionData,$tableData);
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

            $this->addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension.edit',
            );
			
            return $view->renderResponse('ExtensionEdit');
        } else {

            $this->addDocHeaderCloseAndSaveButtons(
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
    			'extensionName' => $extensionName
            ]);

        	return $view->renderResponse('ExtensionEnumerationAdd');
        }
    }


    public function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
        ModuleController::debugRequest($request, 'Enumeration edit');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

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

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        if ($parsedBody['extensionData'] ?? false) {
            $vendorName = $parsedBody['extensionData']['extension']['vendorName'];
            $extensionName = $parsedBody['extensionData']['extension']['extensionName'];
        } else {
            $vendorName = $request->getQueryParams()['vendorName'] ?? '';
            $extensionName = $request->getQueryParams()['extensionName'] ?? '';
		}
        $enumerationName = $request->getQueryParams()['enumerationName'] ?? '';
        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $enumerationData = $extensionData['enumerations'][$enumerationName];

        if (in_array($parsedBody['CMD'] ?? [], ['save',], true)) {
            Tools\ConfigArray::arrayMerge($extensionData,$enumerationData);
debug($extensionData, 'save');
            self::save(
                $parsedBody['vendorName'] ?? '',
                $parsedBody['extensionName'] ?? '',
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
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'extensionData' => $extensionData,
                'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            return $view->renderResponse('ExtensionEdit');
        } else {
            $enumerationData = $extensionData['enumerations'][$enumerationName];
			
            $this->addDocHeaderCloseAndSaveButtons(
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
                'enumerationName' => $enumerationName,
                'enumerationData' => $enumerationData,
            ]);			

            return $view->renderResponse('ExtensionEnumerationEdit');
		}
    }


    public function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        ModuleController::debugRequest($request, 'Enumeration delete');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

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

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'extension',
        );

        if ($parsedBody['extensionData'] ?? false) {
            $vendorName = $parsedBody['extensionData']['extension']['vendorName'];
            $extensionName = $parsedBody['extensionData']['extension']['extensionName'];
        } else {
            $vendorName = $request->getQueryParams()['vendorName'] ?? '';
            $extensionName = $request->getQueryParams()['extensionName'] ?? '';
		}

        $tableName = $request->getQueryParams()['tableName'] ?? '';
        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
		unset($extensionData['tables'][$tableName]);

        self::save(
            $vendorName ?? '',
            $extensionName ?? '',
            $extensionData ?? [],
        );

        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.extension.add.h1',
			'extension.add',
        );

        $view->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);

        return $view->renderResponse('ExtensionEdit');
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
        ModuleController::flashMessage('Vendor: ' . $vendorName, 'Saving extension: ' . $extensionName);
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