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
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($this->configuration, $bodyParams['configuration']);

                $this->writeConfiguration();
                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.configuration.xlf:savingConfiguration',
                    )
                );
                break;
		}

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'builderLocal' => $this->builderLocal,
            'isProKey' => $this->isProKey,
        ]);

        $this->addDocHeaderModuleDropDown(
            'Configuration',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            'list',
            'Extension',
            'configuration-form',
        );

    	return $this->moduleTemplate->renderResponse('Configuration');
    }

}