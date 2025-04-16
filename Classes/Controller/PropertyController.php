<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class PropertyController extends ExtensionBuilderController
{

// ToDo zusammen füheren der ext arrays, sonst werden die alten daten komplet überschriben!

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentsUid = $bodyParams['componentsUid'];
        $componentUid = $bodyParams['componentUid'];

        $propertysUid = $bodyParams['propertysUid'];

        $componentsDev = $this->ebService->extensionConfiguration['components'][$componentsUid];
        $propertysDev = $componentsDev['propertys'][$propertysUid];

        $fields = $propertysDev['fields'];
        $fieldsTabs = $propertysDev['fieldsTabs'] ?? ['general'];
        $fieldsData = $bodyParams['fieldsData'] ?? [];

        $propertyFieldsData = $bodyParams['propertyData'] ?? [];


        $fields = array_merge(
            ['uid' => [
                'type' => 'string',
                'lllPath' => '.componentproperty'
            ]],
            $propertysDev['fields'],
        );
        $propertyFields = $propertysDev['propertyFields'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $propertyUid = $fieldsData['uid'];
                if ($this->ebService->checkUid($this, $propertyUid)) {
                    self::componentWrite(
                        $vendorName,
                        $extensionName,
                        $componentsUid,
                        $componentUid,
                        $propertysUid,
                        $propertyUid,
                        $componentsDev,
                        $propertysDev,
                        $fieldsData,
                        $propertyFieldsData,
                    );
                }
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentsTitle' => $componentsDev['title'] ,
            'componentUid' => $componentUid,

            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',
//            'propertyFields' => $propertyFields,
//            'propertyFieldsTabs' => $propertyFieldsTabs,
//            'propertyFieldsData' => $propertyFieldsData,
//            'propertyFieldsDataName' => 'propertysData',
        ]);

        $this->addDocHeaderCloseButton(
            'edit',
            'Component',
            $vendorName,
            $extensionName,
            componentsUid: $componentsUid,
            componentUid: $componentUid,
        );
        $this->addDocHeaderSaveButton(
            'property-edit-form',
            'Property',
        );

        return $this->moduleTemplate->renderResponse($propertysDev['add']);
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentsUid = $bodyParams['componentsUid'];
        $componentUid = $bodyParams['componentUid'];
        $propertysUid = $bodyParams['propertysUid'];
        $propertyUid = $bodyParams['propertyUid'];

        $componentsDev = $this->ebService->extensionConfiguration['components'][$componentsUid];
        $componentsFields = $componentsDev['fields'];
        $propertysDev = $componentsDev['propertys'][$propertysUid];

        $componentData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName][$componentsUid][$componentUid];

        $fields = $propertysDev['fields'];
        $fieldsTabs = $propertysDev['fieldsTabs'] ?? ['general'];
        $fieldsData = $bodyParams['fieldsData'] ?? $componentData['propertys'][$propertysUid][$propertyUid];
        unset($fieldsData['fields']);

        $propertyFields = $propertysDev['propertyFields'];
        $propertyFieldsTabs = $propertysDev['propertyFieldsTabs'];
        $propertyFieldsData = $bodyParams['propertysData'] ?? $componentData['propertys'][$propertysUid][$propertyUid]['fields'];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                self::componentWrite(
                    $vendorName,
                    $extensionName,
                    $componentsUid,
                    $componentUid,
                    $propertysUid,
                    $propertyUid,
                    $componentsDev,
                    $propertysDev,
                    $fieldsData,
                    $propertyFieldsData,
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentsTitle' => $componentsDev['title'] ,
            'componentUid' => $componentUid,

            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',

            'propertyFields' => $propertyFields,
            'propertyFieldsTabs' => $propertyFieldsTabs,
            'propertyFieldsData' => $propertyFieldsData,
            'propertyFieldsDataName' => 'propertysData',
        ]);

        $this->addDocHeaderCloseButton(
            'edit',
            'Component',
            $vendorName,
            $extensionName,
            componentsUid: $componentsUid,
            componentUid: $componentUid,
        );
        $this->addDocHeaderSaveButton(
            'property-edit-form',
            'Property',
        );

        return $this->moduleTemplate->renderResponse($propertysDev['edit']);
    }

	private function componentWrite(
        string $vendorName,
        string $extensionName,
        string $componentsUid,
        string $componentUid,
        string $propertysUid,
        string $propertyUid,
        array $componentsDev,
        array $propertysDev,
        array $fieldsData,
        array $propertysData = [],
    ): void {
        $componentData =
            &$this->ebService->vendorsAndExtensions
            [$vendorName]['extensions'][$extensionName][$componentsDev['uid']][$componentUid];

        if (!($componentData['propertys'] ?? false)) {
            $componentData['propertys'] = [];
        }

        if (!($componentData['propertys'][$propertysUid] ?? false)) {
            $componentData['propertys'][$propertysUid] = [];
        }

        if (!($componentData['propertys'][$propertysUid][$propertyUid] ?? false)) {
            $componentData['propertys'][$propertysUid][$propertyUid] = [];
        }

//        $componentData['propertys'][$propertysUid][$propertyUid] = $fieldsData;

        if (!($componentData['propertys'][$propertysUid][$propertyUid]['fields'] ?? false)) {
            $componentData['propertys'][$propertysUid][$propertyUid]['fields'] = [];
        }

        if ($propertysData) {
            $propertyDataMerge = $componentData['propertys'][$propertysUid][$propertyUid]['fields'];
            Tools\ConfigArray::arrayMerge($propertyDataMerge, $propertysData);
            $componentData['propertys'][$propertysUid][$propertyUid]['fields'] = $propertyDataMerge;
		}

        $this->ebService->writeExtensionComponent(
            $this,
            $vendorName,
            $extensionName,
            $componentUid,
            $componentData,
            $componentsDev['uid'],
            $componentsDev['title'],
        );
	}

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentsUid = $bodyParams['componentsUid'];
        $componentUid = $bodyParams['componentUid'];
        $propertysUid = $bodyParams['propertysUid'];
        $propertyUid = $bodyParams['propertyUid'];

        $componentsDev = $this->ebService->extensionConfiguration['components'][$componentsUid];
        $componentsFields = $componentsDev['fields'];
        $propertysDev = $componentsDev['propertys'];

        $extensionData = &$this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $componentData = $extensionData[$componentsUid][$componentUid];

	
        $this->ebService->deleteExtensionComponent(
            $this,
            $vendorName,
            $extensionName,
            $componentsUid,
            $componentUid,
            $propertysUid,
            $propertyUid,
        );

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' =>$extensionData,
            'componentsUid' => $componentsUid,
            'componentUid' => $componentUid,
            'propertysDev' => $propertysDev,
            'componentsFields' => $componentsFields,
            'componentTitle' => $componentsDev['title'],
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseButton(
            'edit',
            'Extension',
            $vendorName,
            $extensionName,
            componentsUid: $componentsUid,
            componentUid: $componentUid,
        );
        $this->addDocHeaderSaveButton(
            'component-edit-form',
            'Component',

        );

        return $this->moduleTemplate->renderResponse($componentsDev['edit']);
    }

}