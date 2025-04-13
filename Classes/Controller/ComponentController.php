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

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentsUid = $bodyParams['componentsUid'];

        $componentsDev = $this->ebService->extensionConfiguration['components'][$componentsUid];

        $fields = array_merge(
            ['uid' => [
                'type' => 'string',
                'lllPath' => '.componentproperty'
            ]],
            $componentsDev['fields']
        );
        $fieldsTabs = $componentsDev['fieldsTabs'] ?? ['general'];
        $fieldsData = $bodyParams['fieldsData'] ?? [];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $componentUid = $fieldsData['uid'];
                if ($this->ebService->checkUid($this, $componentUid)) {
                    unset($fieldsData['uid']);
                    $this->ebService->writeExtensionComponent(
                        $this,
                        $vendorName,
                        $extensionName,
                        $componentUid,
                        $fieldsData,
                        $componentsDev['uid'],
                        $componentsDev['title'],
                    );
                    $fieldsData['uid'] = $componentUid;
                }
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentsTitle' => $componentsDev['title'],

            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',
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

        return $this->moduleTemplate->renderResponse($componentsDev['add']);
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentsUid = $bodyParams['componentsUid'];
        $componentUid = $bodyParams['componentUid'];

        $componentsDev = $this->ebService->extensionConfiguration['components'][$componentsUid];
        $componentsFields = $componentsDev['fields'];
        $propertysDev = $componentsDev['propertys'];

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $componentData = $extensionData[$componentsUid][$componentUid];

        $fields = $componentsDev['fields'];
        $fieldsTabs = $componentsDev['fieldsTabs'] ?? ['general'];
        $fieldsData = $bodyParams['fieldsData'] ?? $extensionData[$componentsUid][$componentUid];
        unset($fieldsData['propertys']);
		
        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->ebService->writeExtensionComponent(
                    $this,
                    $vendorName,
                    $extensionName,
                    $componentUid,
                    $fieldsData,
                    $componentsDev['uid'],
                    $componentsDev['title'],
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' =>$extensionData,
            'componentsUid' => $componentsUid,
            'componentUid' => $componentUid,
            'componentData' =>$componentData,
            'propertysDev' => $propertysDev,
            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',
        ]);

        $this->addDocHeaderCloseButton(
            'edit',
            'Extension',
            $vendorName,
            $extensionName,
            componentsUid: $componentsUid,
        );
        $this->addDocHeaderSaveButton(
            'component-edit-form',
            'Component',

        );

        return $this->moduleTemplate->renderResponse($componentsDev['edit']);
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentsUid = $bodyParams['componentsUid'];
        $componentUid = $bodyParams['componentUid'];

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $this->ebService->deleteExtensionComponent(
            $this,
            $vendorName,
            $extensionName,
            $componentsUid,
            $componentUid,
        );

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'componentsDev' =>  $this->ebService->extensionConfiguration['components'],
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
        $this->addDocHeaderBuildButton(
            'build',
            'Extension',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse('Extension/Edit');
    }

}