<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

/**
 * Version 1.0.0 - RC1
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Context\Context;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class ConfigurationModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
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


    /**
     * Module controller
     */
    final function configuration(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

        $view = $this->moduleTemplateFactory->create($request);

		if ( in_array( $bodyParams['CMD'] ?? [], ['save',], true ) ) {
            $this->configuration = $bodyParams['configurationData'];
            $this->writeConfiguration();
            $this->flashMessage('', 'Saving configuration'); // ToDo LLL
		}

        $view->assignMultiple([
            'configurationData' => $this->configuration,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'configuration',
        );

        $this->addDocHeaderCloseandSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );

    	return $view->renderResponse('Configuration');
    }


    /**
     * Saving the configuration settings
     */
    final function writeConfiguration(): void
    {
        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder().
            'TYPO3' . DIRECTORY_SEPARATOR . 'configuration.json';

        $configuration = [];
        $configuration['configuration'] = $this->configuration;

        Tools\Json::write($fileName,$configuration);
	}

}