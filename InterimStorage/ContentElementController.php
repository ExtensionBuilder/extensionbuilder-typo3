<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ContentElementController extends ExtensionBuilderController
{

    public function add(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];

        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $contentElementName = $bodyParams['contentElementData']['name'];
                $contentElementData = $bodyParams['contentElementData'];

                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'contentElements',
                    $contentElementName,
                    $contentElementData,
                );

                $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

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

        $contentElementData = [];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'contentElementData' => $contentElementData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('ContentElement/Add');
    }

    public function edit(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extennionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $contentElementName = $bodyParams['contentElementData']['name'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    $contentElementName,
                    $bodyParams['contentElementData']
                );

                $view->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'contentElement' => $contentElement,
                ]);

                $this->addDocHeaderAddButton(
                    'locallang.extensions.xlf:editExtension',
			        'extension.edit',
                );

                return $view->renderResponse('Extension/Edit');
                break;
		}

        $contentElementData = $extensionData['tables'][$tableName];

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'contentElementData' => $contentElementData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('ContentElement/Edit');
    }

    public function delete(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];

        $this->extensionbuilderObject->deleteExtensionPropoty(
            $vendorName,
            $extensionName,
            $bodyParams['contentElementData']['name'],
            $bodyParams['contentElementData'],
        );


        $view->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'contentElement' => $contentElement,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'extension',
            $vendorName,
            $extensionName,
        );

        return $view->renderResponse('ContentElement/Edit');
    }

}