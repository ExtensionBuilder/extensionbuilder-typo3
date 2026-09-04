<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Backend\Attribute\AsController;

/**
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
final class ComponentController extends ExtensionBuilderController
{
    /**
     * @since 0.12
     */
    public function addAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getQueryParams(), is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []);

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsName = (string)($bodyParams['componentsName'] ?? '');
        $componentsDev = $this->ebBackendService->extensionConfiguration['components'][$componentsName];

        $componentName = [
            'componentName' => [
                'type' => 'input',
                'lllPath' => '.component',
                'tab' => 'general',
                'required' => true,
            ],
        ];

        $fields = array_merge(
            [
                'componentName' => [
                    'type' => 'input',
                    'lllPath' => '.component',
                    'tab' => 'general',
                    'required' => true,
                ],
            ],
            $componentsDev['fields']
        );

        if ($fields['componentName']['type'] == 'select') {
            // Remove an existing selection from the selection options.
            // So that each component is only used once.
            foreach ($this->ebBackendService->extension['components'][$componentsName] ?? [] as $componentsKey => $componentsValue) {
                if ($fields['componentName']['selects'][$componentsKey] ?? false) {
                    unset($fields['componentName']['selects'][$componentsKey]);
                }
            }
        }

        $fieldsTabs = $componentsDev['fieldsTabs'] ?? ['general' => [ 'lllPath' => '.component']];
        $fieldsData = $bodyParams['fieldsData'] ?? [];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->extensionConfiguration['components'][$componentsName]['fields'],
                    $fieldsData,
                );

                // ToDo Check for duplicates using JavaScript

                $componentName = $fieldsData['componentName'];

                $this->ebBackendService->writeExtensionComponent(
                    $this,
                    $vendorName,
                    $extensionName,
                    $componentsName,
                    $componentName,
                    $fieldsData,
                );

                $fieldsData['componentName'] = $componentName; // ToDo ???
                break;
        }

        $this->ebBackendService->getLocalExtensions($this->ebBackendService->extension['components']['extensions'] ?? []);

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'action' => 'add',
            'componentsTitle' => $componentsDev['title'],
            'fields' => $fields,
            'fieldsTabs' => $fieldsTabs,
            'fieldsData' => $fieldsData,
            'fieldsDataName' => 'fieldsData',
            'selections' => $this->ebBackendService->extension['selections'],
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

    /**
     * @since 0.12
     */
    public function editAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getQueryParams(), is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []);

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsName = (string)($bodyParams['componentsName'] ?? '');
        $componentName = (string)($bodyParams['componentName'] ?? '');

        $componentsDev = $this->ebBackendService->extensionConfiguration['components'][$componentsName];
        $componentsFields = $componentsDev['fields'];
        $fields = $componentsDev['fields'];
        $propertysDev = $componentsDev['propertys'] ?? [];

        $extensionData = &$this->ebBackendService->extension['components'];
        $componentData = $extensionData[$componentsName][$componentName];

        $fields = $componentsDev['fields'];
        unset($fields['componentName']);

        $fieldsTabs = $componentsDev['fieldsTabs'] ?? ['general'];

        $fieldsData = $bodyParams['fieldsData'] ?? $extensionData[$componentsName][$componentName];
        unset($fieldsData['propertys']);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->extensionConfiguration['components'][$componentsName]['fields'],
                    $fieldsData,
                );

                $this->ebBackendService->writeExtensionComponent(
                    $this,
                    $vendorName,
                    $extensionName,
                    $componentsName,
                    $componentName,
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
            'extensionData' => $extensionData,
            'componentsName' => $componentsName,
            'action' => 'edit',
            'componentsTitle' => $componentsDev['title'],
            'componentData' => $componentData,
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
        );
        $this->addDocHeaderSaveButton(
            'component-edit-form',
            'Component',
        );

        // ToDo
        $this->addDocHeaderBuildButton(
            'build',
            'Extension',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse($componentsDev['edit']);
    }

    // error
    /**
     * @since 0.12
     */
    public function deleteAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getQueryParams(), is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []);

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = (string)($bodyParams['vendorName'] ?? '');
        $extensionName = (string)($bodyParams['extensionName'] ?? '');

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/modulestate.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsName = (string)($bodyParams['componentsName'] ?? '');
        $componentName = (string)($bodyParams['componentName'] ?? '');

        $this->ebBackendService->deleteExtensionComponent(
            $this,
            $vendorName,
            $extensionName,
            $componentsName,
            $componentName,
        );

        $extensionData = &$this->ebBackendService->extension;
        $componentsDev = $this->ebBackendService->extensionConfiguration['components'];

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'extensionConfiguration' => $this->ebBackendService->extensionConfiguration,
            'configuration' => $this->ebBackendService->configuration,
            'componentsDev' =>  $componentsDev,
            'vendors' => $this->ebBackendService->getVendors(),
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

        return $this->moduleTemplate->renderResponse('ExtensionEdit');
    }

}
