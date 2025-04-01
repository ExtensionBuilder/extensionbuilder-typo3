<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ComponentController extends ExtensionBuilderController
{

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $component = $bodyParams['component'];
        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':

// ToDo  LocalizationUtility::translate($this->ebService->lll .'.extension.xlf:specifyextensionname'),
                if (!preg_match("#^[a-zA-Z0-9]+$#", $bodyParams['componentUid'] ?? '') ) {
                    $this->flashMessage(
                        '',
                        'nur zahlen und bucstaben',
                    );
				}
                if (preg_match("#^[0-9]+$#", $bodyParams['componentUid'] ?? '') ) {
                    $this->flashMessage(
                        '',
                        'mindesetns ein bucstaben',
                    );
                }
                if (!($bodyParams['componentUid'] ?? false)) {
                    $this->flashMessage(
                        '',
                        'keine name',
                    );
				}

                $this->ebService->writeExtensionComponent(
                    $vendorName,
                    $extensionName,
                    $component['uid'],
                    $bodyParams['componentUid'],
                    $bodyParams['componentData'] ?? [],
                );

                break;
		}

        $componentData = [];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentUid' => $component['uid'],
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseButton(
            'edit',
            'Extension',
            $vendorName,
            $extensionName,

        );
        $this->addDocHeaderSaveButton(
            'component-edit-form',
            'Component',
        );

        return $this->moduleTemplate->renderResponse($component['add']);
    }

    public function extensionEdit(): ResponseInterface {

	}

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $component = $bodyParams['component'];
        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentName = $bodyParams['componentName'];
        $componentData = $bodyParams['componentData'];

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->ebService->writeExtensionComponent(
                    $vendorName,
                    $extensionName,
                    $component['uid'],
                    $componentName,
                    $componentData,
                );

                break;
		}

        $componentData = $this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName][$component['uid']][$componentName] ?? [];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentUid' => $componentName,
            'componentData' => $componentData,
        ]);


        $this->addDocHeaderCloseButton(
            'edit',
            'Extension',
            $vendorName,
            $extensionName,

        );
        $this->addDocHeaderSaveButton(
            'component-edit-form',
            'Component',
        );

        return $this->moduleTemplate->renderResponse($component['edit']);
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $component = $bodyParams['component'];
        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $this->ebService->deleteExtensionComponent(
            $vendorName,
            $extensionName,
            $component['uid'],
            $bodyParams['componentName'],
        );

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->ebService->configuration,
            'registeredVendorGroups' => $this->ebService->getRegisteredVendorGroups(),
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
        ]);

        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderSaveButton(
            'extension-edit-form',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Extension/Edit');
    }

}