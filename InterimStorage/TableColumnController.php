<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class TableColumnController extends ExtensionBuilderController
{

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $tableName = $bodyParams['tableName'];

        switch ($bodyParams['cmd'] ?? '') {
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
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Table/Add');
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $tableName = $bodyParams['tableName'];
        $columnName = $bodyParams['columnName'];


//        ModuleController::addDocHeaderModuleDropDown(
//            $view,
//            'extension'
//        );

//        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
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
            'table.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Table/Column/Edit');
    }

// ToDo
//  Duplicate
//  Rename

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $tableName = $bodyParams['tableName'];
        $columnName = $bodyParams['columnName'];


//        $this->addDocHeaderModuleDropDown(
//            $view,
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
            'extension',
            $vendorName,
            $extensionName,
        );

//        $this->addDocHeaderAddButton(
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