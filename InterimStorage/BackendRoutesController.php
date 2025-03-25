<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class BackendRoutesController extends ExtensionBuilderController
{

    public function add(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
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

    public function edit(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $tableName = $bodyParams['tableName'];
        $tableNameUc = ucfirst($tableName);

        switch ($bodyParams['cmd'] ?? '') {
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

    public function delete(): ResponseInterface {

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