<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Core\Utility;
use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

final class InfoModuleController extends BuildExtensionAbstract
{

    public function __construct(
        protected readonly LanguageServiceFactory $languageServiceFactory,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconRegistry $iconRegistry,
        protected readonly IconFactory $iconFactory,
        protected readonly Context $context,
    ) {
        parent::__construct();
    }

    final function info(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
        $this->request = $request;
		$view = $this->moduleTemplateFactory->create($request);

// ToDo
        $getStatus = Tools\RestApiClient::getStatus($this->configuration['builderUrl'] ?? 'https://typo3.extension-builder.dev/');

// ToDo $this->configuration['builderUrl']
        $announcements = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Announcements.json');
        $issues = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Issues.json');
        $todo = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_Todo.json');
        $changeLog = $this->getJsonWithcUrl('https://typo3.extension-builder.dev/TYPO3_ChangeLog.json');

        $this->configuration['version'] = Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');

        $this->configuration['developerCounter'] =
            count(Tools\Folder::scanFolderForFile(Tools\ExtensionbuilderFolder::getExtensionBuilderFolder(), filter: 'developer.') ?? []);

        $this->configuration['vendorCounter'] = count($this->vendors);

        $this->configuration['extensionCounter'] = 0;
        foreach ($this->vendors ?? [] as $vendorName => $vendorsData) {
            $this->configuration['extensionCounter'] =
                $this->configuration['extensionCounter']
                + count($this->vendorsAndExtensions[$vendorName]['extensions'] ?? []);
        }

        $this->configuration['projectCounter'] = count($this->projects ?? []);

        $view->assignMultiple([
              'configuration' => $this->configuration,
              'developer' => $this->developer,
              'getStatus' => $getStatus,
              'announcements' => $announcements,
              'issues' => $issues,
              'todo' => $todo,
              'changeLog' => $changeLog,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'info',
        );
        $this->addDocHeaderCloseButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );

        return $view->renderResponse('Info');
    }

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