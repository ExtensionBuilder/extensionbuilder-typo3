<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ViewHelperController extends ExtensionBuilderController
{

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $componentData = $bodyParams['componentData'];

                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'viewHelpers',
                    $componentData['name'],
                    $componentData
                );

    		    $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'extensionData' => $extensionData,
                    'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
    		    ]);

                $this->addDocHeaderCloseAndSaveButtons(
                    'extension.edit',
                );
			
                return $view->renderResponse('Extension/Edit');
                break;
		}

        $componentData = [];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('ComponentAdd');
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $componentName = $bodyParams['componentName'];
        $componentData = $bodyParams['componentData'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'componentName' => $componentName,
                    'componentData' => $componentData,
                ]);

                $this->addDocHeaderAddButton(
                    'locallang.extensions.xlf:editExtension',
			        'extension.edit',
                );

                return $view->renderResponse('Extension/Edit');
                break;
		}

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentName' => $componentName,
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('ViewHelper/Edit');
    }

    public function renameAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

// ToDo

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

        return $view->renderResponse('Extension/Edit');
    }

    public function duplicateAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

// ToDo

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

        return $view->renderResponse('Extension/Edit');
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        unset($extensionData['viewHelpers'][$bodyParams['componentName']]);

        $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;
        $this->extensionbuilderObject->writeExtension($vendorName, $extensionName);

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

        return $view->renderResponse('Extension/Edit');
    }

}