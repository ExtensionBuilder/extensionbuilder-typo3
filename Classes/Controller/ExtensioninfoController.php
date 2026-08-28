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
final class ExtensionInfoController extends ExtensionBuilderController
{

    /**
     * @since 0.12
     */
    public function infoAction(): ResponseInterface {
		$bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $this->ebBackendService->readExtension($vendorName, $extensionName);

        $componentsDev = $this->ebBackendService->extensionConfiguration['components'];
        $extensionData = &$this->ebBackendService->extension;

        $extension = $extensionData['extension'];
        $components = [];
        foreach ($componentsDev ?? [] as $componentName => $componentData ) {
            if ($extensionData['components'][$componentName] ?? false) {
                $components[$componentName] = $extensionData['components'][$componentName];
            }
        }

        $this->moduleTemplate->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extension' => json_encode($extension),
            'components' => json_encode($components),
        ]);

        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('ExtensionInfo/Extension');
    }

}