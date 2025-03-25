<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class CommandController extends ExtensionBuilderController
{

    public function add(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'commands',
                    $bodyParams['componentData']['name'],
                    $bodyParams['componentData']
                );

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

        $componentData = [];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('ComponentAdd');
    }

    public function edit(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':

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
            'schedulerName' => $schedulerName,
            'schedulerData' => $schedulerData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('Command/Edit');
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

        return $view->renderResponse('Command/Edit');
    }

}