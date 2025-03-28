<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class SchedulerController extends ExtensionBuilderController
{

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
debug($bodyParams);
        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->extensionbuilderObject->writeExtensionPropoty(
                    $vendorName,
                    $extensionName,
                    'schedulers',
                    $bodyParams['schedulerData']['name'],
                    $bodyParams['schedulerData']
                );

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
            'scheduler-add-form',
            $vendorName,
            $extensionName,
        );
			
                return $this->moduleTemplate->renderResponse('Extension/Edit');
                break;
		}

        $schedulerData = [];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'schedulerData' => $schedulerData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'scheduler-add-form',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse('Scheduler/Add');
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $vendorName = $bodyParams['vendorName'];
        $extensionName = $bodyParams['extensionName'];
        $extensionData = $this->extensionbuilderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                $this->moduleTemplate->assignMultiple([
                    'configuration' => $this->configuration,
                    'vendorName' => $vendorName,
                    'extensionName' => $extensionName,
                    'schedulerName' => $schedulerName,
                    'schedulerData' => $schedulerData,
                ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'scheduler-edit-form',
            $vendorName,
            $extensionName,
        );

                return $view->renderResponse('Scheduler/Edit');

                break;
		}

        $tableData = $extensionData['tables'][$tableName];

        $this->moduleTemplate->assignMultiple([
            'configuration' => $this->configuration,
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'schedulerName' => $schedulerName,
            'schedulerData' => $schedulerData,
        ]);

        $this->addDocHeaderCloseAndSaveButtons(
            'edit',
            'Extension',
            'scheduler-edit-form',
            $vendorName,
            $extensionName,
        );


        return $this->moduleTemplate->renderResponse('Scheduler/Edit');
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
            'extension',
            $vendorName,
            $extensionName,
        );

        return $this->moduleTemplate->renderResponse('Scheduler/Edit');
    }

}