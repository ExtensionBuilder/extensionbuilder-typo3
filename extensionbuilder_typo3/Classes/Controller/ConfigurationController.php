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
final class ConfigurationController extends ExtensionBuilderController
{

    /**
     * @since 0.12
     */
    function createSystemId(): string
    {
        $data = [
            'host' => $_SERVER['HTTP_HOST'] ?? '',
            'server_name' => $_SERVER['SERVER_NAME'] ?? '',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? '',
            'php_uname' => php_uname('n'),
        ];

        return hash('sha256', json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @since 0.12
     */
    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->configurationConfiguration['fieldsEdit'],
                    $bodyParams['configuration']
                );

                Tools\ConfigArray::arrayMerge($this->ebBackendService->configuration, $bodyParams['configuration']);

                $this->ebBackendService->writeConfiguration();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebBackendService->lll . '.configuration.xlf:savingConfiguration',
                    )
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configurationData' => $this->ebBackendService->configuration,
            'configurationConfiguration' => $this->ebBackendService->configurationConfiguration,
            'builderLocal' => $this->ebBackendService->builderLocal,
            'isProKey' => $this->isProKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Configuration',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderSaveButton(
            'configuration-form',
            'Configuration',
        );

    	return $this->moduleTemplate->renderResponse('Configuration');
    }

}