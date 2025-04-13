<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ConfigurationController extends ExtensionBuilderController
{

    final function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($this->ebService->configuration, $bodyParams['configuration']);

                $this->ebService->writeConfiguration();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebService->lll . '.configuration.xlf:savingConfiguration',
                    )
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'configurationFields' => $this->ebService->configurator['configuration']['fields'],
            'builderLocal' => $this->ebService->builderLocal,
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