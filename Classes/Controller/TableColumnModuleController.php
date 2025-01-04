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

use TYPO3\CMS\Core\Page\PageRenderer;

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;
use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;

use TYPO3\CMS\Backend\Attribute\AsController;

#[AsController]
final class TableColumnModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
{

    public BuildExtension $extensionbuilderObject;

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

        $this->extensionbuilderObject = new BuildExtension;
    }

    public function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $tableName = $bodyParams['tableName'];

        switch ($bodyParams['action'] ?? '') {
            case 'save':

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
                'tableName' => $tableName,
                'extensionData' => $extensionData,
		    	'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            ModuleController::addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension.edit',
            );
			
            return $view->renderResponse('Table/Edit');

                break;
		}


        $tableData = [];
        $tableData['makeSql'] = true;
        $tableData['makeModel'] = true;
        $tableData['makeTca'] = true;
        $tableData['makeFluid'] = true;
        $tableData['language'] = [];

        $view->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'tableData' => $tableData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Table/Add');
    }


    public function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $tableName = $bodyParams['tableName'];
        $columnName = $bodyParams['columnName'];


//        ModuleController::addDocHeaderModuleDropDown(
//            $view,
//            $this->uriBuilder,
//            'extension'
//        );

//        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['action'] ?? '') {
            case 'save':

//            $tmpNode = 'tables';
//            $tmpName = $tableName;

//    		$tmpData = [];
//    		$tmpData[$tmpNode] = [];
//    		$tmpData[$tmpNode][$tmpName] = [];
//    		$tmpData[$tmpNode][$tmpName] = $parsedBody['tableData'] ?? [];
			
//            Tools\ConfigArray::arrayMerge($extensionData,$tmpData);
	

//            self::save(
//                $parsedBody['vendorName'] ?? '',
//                $parsedBody['extensionName'] ?? '',
//                $extensionData ?? [],
//            );

            $view->assignMultiple([
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'tableName' => $tableName,
                'columnName' => $columnName,
                'extensionData' => $extensionData,
                'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.extensions.xlf:addExtension',
			    'extension.add',
            );

            return $view->renderResponse('Tablle/Edit');

            break;
        }


//            $tableData = $extensionData['tables'][$tableName];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'tableName' => $tableName,
//            'tableData' => $tableData,
        ]);			

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'table.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Table/Column/Edit');
    }


// ToDo
//  Duplicate
//  Rename

    public function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $tableName = $bodyParams['tableName'];
        $columnName = $bodyParams['columnName'];


//        $this->addDocHeaderModuleDropDown(
//            $view,
//            $this->uriBuilder,
//            'extension',
//        );

        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

		unset( $extensionData[$tmpNode][$tmpName] );

//        self::save(
//            $vendorName ?? '',
//            $extensionName ?? '',
//            $extensionData ?? [],
//        );

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
            'extension',
            $vendorName,
            $extensionName,
        );

//        $this->addDocHeaderAddButton(
//            $view,
//            $this->iconFactory,
//            $this->uriBuilder,
//            'locallang.extensions.xlf:addExtension',
//			 'extension.add',
//        );

        return $view->renderResponse('Tabe/Edit');
    }


    // ------------------------------------------------------------------


    protected function save(
        string $vendorName,
        string $extensionName,
        array $extensionData,
    ): void {

        $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;
        $this->extensionbulderObject->write( $vendorName, $extensionName );

        ModuleController::flashMessage( 'Vendor: ' . $vendorName, 'Saving extension: ' . $extensionName );
    }

}