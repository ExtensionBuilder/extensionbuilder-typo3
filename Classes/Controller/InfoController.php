<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Utility;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Core\Environment;

#[AsController]
final class InfoController extends ExtensionBuilderController
{

    final function showAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);


        $this->coreStatus = Tools\RestApiClient::getStatus(
            $this->ebService->configuration['builderUrl'],
            $this->ebService->configuration['builderApi'],
	    );


// ToDo
        $announcements = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Announcements.json');
        $issues = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Issues.json');
        $todo = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Todo.json');
        $changeLog = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_ChangeLog.json');

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
        $this->addDocHeaderCloseButtons(
            'list',
            'Extension',
        );

        return $this->moduleTemplate->renderResponse('Info');
    }

// ToDo Move to Tools
    private function getJsonWithcUrl(
        string $url,
    ): array {
        $return = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output) {
            $return = (array)json_decode($output, true);
            if (json_last_error() === 0) {
                $return = array_values($return);
                $return = $return[0];
			}
		}
        return $return;
	}

}