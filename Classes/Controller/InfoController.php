<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Utility;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class InfoController extends ExtensionBuilderController
{

    final function showAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->coreStatus = Tools\RestApiClient::getStatus(
            $this->ebService->configuration['typo3']['builderUrl'],
            $this->ebService->configuration['typo3']['builderApi'],
	    );

        $announcements = Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Announcements.json');
        $issues = Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Issues.json');
        $todo = Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Todo.json');
        $changeLog = Tools\Json::getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_ChangeLog.json');

        $this->ebService->configuration['version'] = Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');
        $this->ebService->configuration['developerCounter'] = $this->ebService->countDeveloper();
        $this->ebService->configuration['vendorCounter'] = count($this->ebService->vendors);
        $this->ebService->configuration['projectCounter'] = count($this->ebService->projects ?? []);
        $this->ebService->configuration['extensionCounter'] = 0;

        foreach ($this->ebService->vendors ?? [] as $vendorName => $vendorsData) {
            $this->ebService->configuration['extensionCounter'] =
                $this->ebService->configuration['extensionCounter']
                + count($this->ebService->vendorsAndExtensions[$vendorName]['extensions'] ?? []);
        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebService->lll,
            'configuration' => $this->ebService->configuration,
            'developer' => $this->ebService->developer,
            'coreStatus' => $this->coreStatus,
            'builderLocal' => $this->ebService->builderLocal,
            'isProKey' => $this->isProKey,
            'keyStatus' => $this->keyStatus,
            'announcements' => $announcements,
            'issues' => $issues,
            'todo' => $todo,
            'changeLog' => $changeLog,
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