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

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

final class ConfigurationModuleController extends BuildExtensionAbstract
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

    final function configuration(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $bodyParams = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
        $view = $this->moduleTemplateFactory->create($request);

        switch ($bodyParams['action'] ?? '') {
            case 'save':
                Tools\ConfigArray::arrayMerge($this->configuration, $bodyParams['configuration']);

                $this->writeConfiguration();
                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $request,
                        'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.configuration.xlf:savingConfiguration',
                    )
                );
                break;
            case 'register':
                echo "register";

$multipart = [];

//$myuuid = Tools\Uuid::uuid();
// ['configuration']['systemId']

//        $resultCode = Tools\RestApiClient::check(
        $resultCode = Tools\RestApiClient::register(
           'https://development.extension-builder.dev/',
//           'https://typo3.extension-builder.dev/',
           $multipart
        );


                break;
		}

// ToDo
$validProKey = false;

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'validProKey'=> $validProKey,
            'builderUrl' => $builderUrl = Setup\Config::BUILDERURI,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'configuration',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );

    	return $view->renderResponse('Configuration');
    }

}