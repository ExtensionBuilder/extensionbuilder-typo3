<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

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

class InfoModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
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
    ): ResponseInterface
    {
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

		$view = $this->moduleTemplateFactory->create($request);

        $this->configuration['version'] = Utility\ExtensionManagementUtility::getExtensionVersion(Setup\GlobalConfig::EXT_NAME);

        $this->configuration['developerCounter'] =
            count(Tools\Folder::scanFolderForFile(Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()) ?? []);

        $this->configuration['vendorCounter'] = count($this->vendors);

        $this->configuration['extensionCounter'] = 0;
        foreach ($this->vendors ?? [] as $vendorName => $vendorsData) {
            $this->configuration['extensionCounter'] =
                $this->configuration['extensionCounter']
                + count($this->vendorsAndExtensions[$vendorName]['extensions'] ?? []);
        }

        $view->assignMultiple([
              'ebConfig' => $this->configuration,
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

}