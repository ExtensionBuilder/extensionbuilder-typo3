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
final class BackendRoutesModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
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

        $this->extensionbuilderObject = new \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtension;
    }

    public function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['action'] ?? '') {
            case 'save':
                $tableName = strtolower($bodyParams['tableData']['name']);
        		$tableNameUc = ucfirst($tableName);

                if (!($extensionData['tables'] ?? false)) { $extensionData['tables'] = []; }

                $extensionData['tables'][$tableName] = [];
                $extensionData['tables'][$tableName] = $bodyParams['tableData'];

// ToDo
//                self::save(
//                    $vendorName,
//                    $extensionName,
//                    $extensionData,
//                );

    		    $view->assignMultiple([
                    'configuration' => $this->configuration,
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
			
                return $view->renderResponse('Extension/Edit');
                break;
		}

        $tableData = [];
        $tableData['makeSql'] = true;
        $tableData['makeModel'] = true;
        $tableData['makeTca'] = true;
        $tableData['makeFluid'] = true;
        $tableData['pagination'] = false;

        $view->assignMultiple([
            'configuration' => $this->configuration,
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
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $tableName = $bodyParams['tableName'];
        $tableNameUc = ucfirst($tableName);

        switch ($bodyParams['action'] ?? '') {
            case 'save':

//                $tmpNode = 'tables';
//        		$tmpName = $tableName;

//        		$tmpData = [];
//        		$tmpData[$tmpNode] = [];
//    	    	$tmpData[$tmpNode][$tmpName] = [];
//    		    $tmpData[$tmpNode][$tmpName] = $parsedBody['tableData'] ?? [];
			
//            Tools\ConfigArray::arrayMerge($extensionData,$tmpData);
	
// ToDo
//            self::save(
//                $parsedBody['vendorName'] ?? '',
//                $parsedBody['extensionName'] ?? '',
//                $extensionData ?? [],
//            );

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'extensionData' => $extensionData,
                    'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
                ]);

                $this->addDocHeaderAddButton(
                    $view,
                    $this->iconFactory,
                    $this->uriBuilder,
                    'locallang.extensions.xlf:editExtension',
			        'extension.edit',
                );

                return $view->renderResponse('Extension/Edit');
                break;
		}

        $tableData = $extensionData['tables'][$tableName];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'tableName' => $tableName,
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

        return $view->renderResponse('Table/Edit');
    }

    public function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $tableName = $bodyParams['tableName'];
        $tableNameUc = ucfirst($tableName);

		unset($extensionData['tables'][$tableName]);

// ToDo
//        self::save(
//            $vendorName,
//            $extensionName,
//            $extensionData,
//        );

        $view->assignMultiple([
            'configuration' => $this->configuration,
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

        return $view->renderResponse('Extension/Edit');
    }

    // ------------------------------------------------------------------

    protected function save(
        string $vendorName,
        string $extensionName,
        array $extensionData,
    ): void {

        $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;
        $this->extensionbuilderObject->writeExtension($vendorName, $extensionName);

        $this->flashMessage('Vendor: ' . $vendorName, 'Saving extension: ' . $extensionName . ' - Table');
    }

}