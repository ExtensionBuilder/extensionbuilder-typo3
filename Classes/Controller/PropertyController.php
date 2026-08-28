<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */

#[AsController]
final class PropertyController extends ExtensionBuilderController
{

    /**
     * @since 0.12
     */
    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $componentName = (string)($bodyParams['componentName'] ?? '');
        $componentsName = (string)($bodyParams['componentsName'] ?? '');
        $propertysName = (string)($bodyParams['propertysName'] ?? '');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsDev = $this->ebBackendService->extensionConfiguration['components'][$componentsName];
        $propertysDev = $componentsDev['propertys'][$propertysName];

        $fields = array_merge(
            [
                'propertyName' => [
                    'type' => 'input',
                    'lllPath' => '.property',
                    'tab' => 'general',
                    'required' => true,
                ]
            ],
            $propertysDev['fields'],
        );

        $fieldsTabs = $propertysDev['fieldsTabs'] ?? ['general' => [ 'lllPath' => '.property']];
        $fieldsData = $bodyParams['fieldsData'] ?? [];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->extensionConfiguration['components'][$componentsName]['propertys'][$propertysName]['fields'],
                    $fieldsData,
                );

                $propertyName = $fieldsData['propertyName'];

// ToDo checkName als JS

                $this->ebBackendService->writeExtensionProperty(
                    $this,
                    $vendorName,
                    $extensionName,
                    $componentsName,
                    $componentName,
                    $propertysName,
                    $propertyName,
                    $fieldsData,
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentName' => $componentName,
            'componentsTitle' => $componentsDev['title'] . 'test' ,
            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',
            'selections' => $this->ebBackendService->extension['selections'],

        ]);

        $this->addDocHeaderCloseButton(
            'edit',
            'Component',
            $vendorName,
            $extensionName,
            componentsName: $componentsName,
            componentName: $componentName,
        );
        $this->addDocHeaderSaveButton(
            'property-edit-form',
            'Property',
        );

        return $this->moduleTemplate->renderResponse($propertysDev['add']);
    }

    /**
     * @since 0.12
     */
    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $componentName = (string)($bodyParams['componentName'] ?? '');
        $componentsName = (string)($bodyParams['componentsName'] ?? '');
        $propertyName = (string)($bodyParams['propertyName'] ?? '');
        $propertyNameUc = ucfirst((string)($bodyParams['propertyName'] ?? ''));
        $propertysName = (string)($bodyParams['propertysName'] ?? '');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/monacoeditor.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsDev = $this->ebBackendService->extensionConfiguration['components'][$componentsName];
        $propertysDev = $this->ebBackendService->extensionConfiguration['components'][$componentsName]['propertys'][$propertysName];
        $componentsFields = $componentsDev['fields'];
        $propertysDev = $componentsDev['propertys'][$propertysName];
        $componentData = &$this->ebBackendService->extension['components'][$componentsName][$componentName];
        $fields = $propertysDev['fields'];
        $fieldsTabs = $propertysDev['fieldsTabs'] ?? ['general'];
        $fieldsData = $bodyParams['fieldsData'] ?? $componentData['propertys'][$propertysName][$propertyName];
        unset($fieldsData['fields']);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->extensionConfiguration['components'][$componentsName]['propertys'][$propertysName]['fields'],
                    $fieldsData,
                );

                $this->ebBackendService->writeExtensionProperty(
                    $this,
                    $vendorName,
                    $extensionName,
                    $componentsName,
                    $componentName,
                    $propertysName,
                    $propertyName,
                    $fieldsData,
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentsTitle' => $componentsDev['title'] ,
            'componentName' => $componentName,
            'propertysTitle' => $propertysDev['title'] ,
            'propertyName' => $propertyName,
            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',
        ]);

// ToDo Code & Doku

//        $fileName =
//            $componentsDev['path']
//            . $componentsDev['propertys'][$propertysName]['path']
//            . $propertyNameUc
//            . $componentsDev['propertys'][$propertysName]['fileEnd']
//            . '.php';

        $this->moduleTemplate->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
//            'fileName' => $fileName,
            'language' => 'php',
        ]);



        $this->addDocHeaderCloseButton(
            'edit',
            'Component',
            $vendorName,
            $extensionName,
            componentsName: $componentsName,
            componentName: $componentName,
        );
        $this->addDocHeaderSaveButton(
            'property-edit-form',
            'Property',
        );

        return $this->moduleTemplate->renderResponse($propertysDev['edit']);
    }

    /**
     * @since 0.12
     */
    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsName = $bodyParams['componentsName'];
        $componentName = $bodyParams['componentName'];
        $propertysName = $bodyParams['propertysName'];
        $propertyName = $bodyParams['propertyName'];

        $this->ebBackendService->deleteExtensionProperty(
            $this,
            $vendorName,
            $extensionName,
            $componentsName,
            $componentName,
            $propertysName,
            $propertyName,
        );

        $componentsDev = $this->ebBackendService->extensionConfiguration['components'][$componentsName];
        $componentsFields = $componentsDev['fields'];
        $fields = $componentsDev['fields'];
        $propertysDev = $componentsDev['propertys'];

        $extensionData = &$this->ebBackendService->extension['components'];
        $componentData = $extensionData[$componentsName][$componentName];
        $fields = $componentsDev['fields'];
        $fieldsTabs = $componentsDev['fieldsTabs'] ?? ['general'];
        $fieldsData = $extensionData[$componentsName][$componentName];
        unset($fieldsData['propertys']);

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentName' => $componentName,
            'extensionData' =>$extensionData,
            'componentsName' => $componentsName,
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
            componentsName: $componentsName,
            componentName: $componentName,
        );
        $this->addDocHeaderSaveButton(
            'component-edit-form',
            'Component',
        );

        return $this->moduleTemplate->renderResponse($componentsDev['edit']);
    }

}