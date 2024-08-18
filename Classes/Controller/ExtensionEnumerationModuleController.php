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
use TYPO3\CMS\Core\Imaging\IconRegistry;

use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
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

class ExtensionEnumerationModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
{

    public \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension $extensionbulderObject;

    public array $projects = [];

    public function __construct(
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
        ServerRequestInterface $request
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Enumeration add');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);
        $view->assign(
            'vdateFormat',
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

            $tmpNode = 'enumerations';
    		$tmpName = strtolower($parsedBody['enumerationName']);

//    		$tmpNameUc = ucfirst($tmpName);
//           $tmpName = strtoupper($tmpName);

    		$tmpData = [];
    		$tmpData[$tmpNode] = [];
    		$tmpData[$tmpNode][$tmpName] = [];

    		if ($parsedBody['enumerationData']['description'] ?? false) {
    		    $tmpData[$tmpNode][$tmpName]['description'] = $parsedBody['enumerationData']['description'];
    		}

    		Tools\ConfigArray::arrayMerge($extensionData,$tmpData);

            self::save(
                $vendorName ?? '',
                $extensionName ?? '',
                $extensionData ?? []
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
                'extension.edit',
            );
			
            return $view->renderResponse('ExtensionEdit');
        } else {

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName
            ]);

            ModuleController::addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension.edit',
                $vendorName,
                $extensionName
            );

        	return $view->renderResponse('ExtensionEnumerationAdd');
        }
    }


    public function edit(
        ServerRequestInterface $request
    ): ResponseInterface {
        ModuleController::debugRequest($request, 'Enumeration edit');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';
        $enumerationName = $queryParams['enumerationName'] ?? '';

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

        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        if (in_array($parsedBody['CMD'] ?? [], ['save',], true)) {
      
            $tmpNode = 'enumerations';
    		$tmpName = strtolower($enumerationName);

    		$tmpData = [];
    		$tmpData[$tmpNode] = [];
    		$tmpData[$tmpNode][$tmpName] = [];
    		$tmpData[$tmpNode][$tmpName] = $parsedBody['enumerationData'] ?? [];
			
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

            $enumerationData = $extensionData['enumerations'][$enumerationName];

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
                'enumerationName' => $enumerationName,
                'enumerationData' => $enumerationData,
            ]);			
            return $view->renderResponse('ExtensionEnumerationEdit');
		}
    }


    public function delete(
        ServerRequestInterface $request
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Enumeration delete');

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';

        $tmpNode = 'enumerations';
        $tmpName = $request->getQueryParams()['enumerationName'] ?? "";

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
            $extensionName,
        );
        return $view->renderResponse( 'ExtensionEdit' );
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
        foreach ($this->extensionbulderObject->vendorsAndExtensions ?? [] as $vendor) {
            $tmpArray[] = $vendor['vendorName'];
        }
        return $tmpArray;
    }

}