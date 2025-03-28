<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class CommandController extends ExtensionBuilderController
{

// ToDo errorAction ?

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

//debug($bodyParams);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'commands',
                    $bodyParams['componentData']['name'],
                    $bodyParams['componentData']
                );

    		    $this->moduleTemplate->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'extensionData' => $extensionData,
                    'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
    		    ]);

//return $this->redirect('edit', 'Extension');

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'command-add-form',
            $vendorName,
            $extensionName,
        );
			
                return $this->moduleTemplate->renderResponse('Extension/Edit');
                break;
		}

        $componentData = [];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'command-add-form',
            $vendorName,
            $extensionName,
        );


        return $this->moduleTemplate->renderResponse('ComponentAdd');
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

debug($bodyParams,'CommandController.php - editAction()');

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $componentName = $bodyParams['componentName'];
        $componentData = $bodyParams['componentData'];

//        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'commands',
                    $bodyParams['componentData']['name'],
                    $bodyParams['componentData']
                );



                $this->moduleTemplate->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'extensionData' => $extensionData,
                    'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
                ]);

 return $this->redirect('edit', 'Extension');


                $this->addDocHeaderAddButton(
                    $this->iconFactory,
                    $this->uriBuilder,
                    'locallang.extensions.xlf:editExtension',
			        'extension.edit',
                );

                return $this->moduleTemplate->renderResponse('Extension/Edit');
                break;
		}
//debug($this->vendorsAndExtensions[$vendorName]);
debug($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['commands']);

        $commandData = $this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['commands'] ?? [];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'componentName' => $componentName,
            'componentData' => $componentData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'command-edit-form',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse('Command/Edit');
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'command-edit-form',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse('ComponentEdit');
    }

}