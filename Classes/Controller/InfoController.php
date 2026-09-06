<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Backend\Attribute\AsController;

use TYPO3\CMS\Core\Utility;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
#[AsController]
final class InfoController extends ExtensionBuilderController
{
    /**
     * @since 0.12
     */
    final public function showAction(): ResponseInterface
    {
        $bodyParams = array_merge(
            $this->request->getQueryParams(),
            is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []
        );

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        $this->coreStatus = Tools\RestApiClient::getStatus(
            $this->ebBackendService->configuration['typo3']['builderUrl'] ?? '',
            $this->ebBackendService->configuration['typo3']['builderApi'] ?? '',
        );

        $this->coreDevStatus = Tools\RestApiClient::getStatus(
            $this->ebBackendService->configuration['typo3']['builderDevUrl'] ?? '',
            $this->ebBackendService->configuration['typo3']['builderDevApi'] ?? '',
        );

        $this->ebBackendService->configuration['version']
            = Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');

        $this->ebBackendService->configuration['developerCounter'] = $this->ebBackendService->countDeveloper();
        $this->ebBackendService->configuration['vendorCounter'] = count($this->ebBackendService->vendors ?? []);
        $this->ebBackendService->configuration['projectCounter'] = count($this->ebBackendService->projects ?? []);
        $this->ebBackendService->configuration['extensionCounter'] = 0;

        foreach ($this->ebBackendService->vendors ?? [] as $vendorName => $vendorsData) {
            $this->ebBackendService->configuration['extensionCounter']
                = $this->ebBackendService->configuration['extensionCounter']
                + count($this->ebBackendService->vendorsAndExtensions[$vendorName]['extensions'] ?? []);
        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'coreStatus' => $this->coreStatus,
            'coreDevStatus' => $this->coreDevStatus,
            'builderLocal' => $this->ebBackendService->builderLocal,
            'builderLocalVersion' => $this->ebBackendService->builderLocalVersion,

            // ToDo
            'isProKey' => $this->isProKey,
            'keyStatus' => $this->keyStatus,

            'generatingCodeFor' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_GeneratingCodeFor.json'),
            'announcements' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Announcements.json'),
            'issuesEditor' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Editor_Issues.json'),
            'todoEditor' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Editor_Todo.json'),
            'changeLogEditor' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Editor_ChangeLog.json'),
            'issuesCore' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Core_Issues.json'),
            'todoCore' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Core_Todo.json'),
            'changeLogCore' => Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_V1_Core_ChangeLog.json'),
        ]);

        $this->addDocHeaderModuleDropDown(
            'Info',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Info');
    }
}