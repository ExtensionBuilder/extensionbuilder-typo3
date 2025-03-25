<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class PluginController extends ExtensionBuilderController
{

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $pluginData = $bodyParams['pluginData'];

                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'plugins',
                    $pluginData['name'],
                    $pluginData
                );

    		    $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'extensionData' => $extensionData,
                    'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
    		    ]);

                $this->addDocHeaderCloseAndSaveButtons(
                    $this->iconFactory,
                    $this->uriBuilder,
                    'extension.edit',
                );

                return $view->renderResponse('Extension/Edit');
                break;
		}

        $pluginData = [];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'pluginData' => $pluginData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Plugin/Add');
    }

    public function edit(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

		$view = $this->moduleTemplateFactory->create($request);

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
                    'extensionData' => $extensionData,
                    'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Plugin/Edit');
    }

    public function delete(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $view->assignMultiple([
                    'configuration' => $this->configuration,
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

        return $view->renderResponse('Plugin/Edit');
    }

}