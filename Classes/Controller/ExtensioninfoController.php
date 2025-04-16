<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class ExtensioninfoController extends ExtensionBuilderController
{

    public function infoAction(): ResponseInterface {
		$bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
//        $componentsUid = $bodyParams['componentsUid'] ?? '';
//        $componentUid = $bodyParams['componentUid'] ?? '';

        $componentsDev = $this->ebService->extensionConfiguration['components'];

        $extensionData = $this->ebService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $extension = $extensionData['extension'];
        $components = [];
        foreach ($componentsDev ?? [] as $componentName => $componentData ) {
            if ($extensionData[$componentName] ?? false) {
                $components[$componentName] = $extensionData[$componentName];
            }
        }

//debug($extension);
//debug($components);

        $this->moduleTemplate->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extension' => json_encode($extension),
            'components' => json_encode($components),
        ]);

// ToDo 
        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
//            $vendorName,
//            $extensionName,
        );
//        $this->addDocHeaderSaveButton(
//            'component-edit-form',
//            'Component',
//        );

        return $this->moduleTemplate->renderResponse('ExtensionInfo/Extension.html');
    }

}