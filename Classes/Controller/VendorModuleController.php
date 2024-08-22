<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;

// FlashMessage
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class VendorModuleController extends \ExtensionBuilder\ExtensionbuilderTypo3\BuildExtensionAbstract
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


    final function list(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
//        ModuleController::debugRequest($request, 'Extension - List');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

		$view = $this->moduleTemplateFactory->create($request);

        $view->setTitle(
            $GLOBALS['LANG']->sL(
                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab' ),
            $GLOBALS['LANG']->sL(
                'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.extension' ),
        );

        if (!$this->developer) {
            $view->assignMultiple([
                'developer' => $this->developer,
            ]);

            $this->addDocHeaderModuleDropDown(
                $view,
                $this->uriBuilder,
                'developer',
            );
            $this->addDocHeaderCloseAndSaveButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension',
            );

            return $view->renderResponse('Developer');
        } else {
            $view->assignMultiple([
                'vendorList' => $this->vendors,
            ]);

            $this->addDocHeaderModuleDropDown(
                $view,
                $this->uriBuilder,
                'vendor'
            );
            $this->addDocHeaderCloseButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension',
            );
            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.vendor.add.h',
	    		'vendor.add',
            );

    	    return $view->renderResponse('VendorList');
		}
    }


    final function add(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Add');

        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);
debug($bodyParams);

        $vendorName = $bodyParams['vendorName'] ?? '';
        $vendorData = $bodyParams['vendorData'] ?? [];


		$view = $this->moduleTemplateFactory->create($request);


//        $view->setTitle(
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_modules.xlf:mlang_tabs_tab'),
//            $languageService->sL('LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:function.add')
//        );

        if (in_array($bodyParams['CMD'] ?? [], ['save',], true)) {

            Tools\ConfigArray::arrayMerge($vendorData, $bodyParams['vendorData']);

            self::saveVendor($vendorName);

            $view->assignMultiple([
                'vendorList' => $this->vendors,
            ]);

            $this->addDocHeaderModuleDropDown(
                $view,
                $this->uriBuilder,
                'vendor',
            );
            $this->addDocHeaderCloseButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension',
            );
            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.vendor.add.h',
			    'vendor.add',
            );

            return $view->renderResponse('VendorList');
        }

        $vendorData = [
            'vendorName' => '',
            'company' => '',
            'address' => '',
            'zip' => '',
            'city' => '',
            'country' =>  '',
            'salsConatct' => '',
            'salsConatctEmail' => '',
            'salsConatctPhone' => '',
            'supportConatct' => '',
            'supportConatctEmail' => '',
            'supportConatctPhone' => '',
            'homepage' => '',
            'description' => '',
        ];

        $view->assignMultiple([
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
             $this->uriBuilder,
            'vendor',
        );

        return $view->renderResponse('VendorAdd');
    }


    final function edit(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Edit');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

        $vendorName = $bodyParams['vendorName'] ?? '';

        $vendorData = $this->vendors[$vendorName];

        $view = $this->moduleTemplateFactory->create($request);

        if (in_array($parsedBody['CMD'] ?? [], ['save',], true)) {

            Tools\ConfigArray::arrayMerge($vendorData, $parsedBody['vendorData']);

            self::saveVendor($parsedBody['vendorName'] ?? '');

            $view->assignMultiple([
                'vendorList' => $this->vendors,
            ]);

            $this->addDocHeaderModuleDropDown(
                $view,
                $this->uriBuilder,
                'vendor',
            );
            $this->addDocHeaderCloseButtons(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'extension',
            );
            $this->addDocHeaderAddButton(
                $view,
                $this->iconFactory,
                $this->uriBuilder,
                'locallang.xlf:function.vendor.add.h',
			    'vendor.add',
            );

            return $view->renderResponse('VendorList');
        }

        $view->assignMultiple([
            'vendorData' => $vendorData,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );   
        $this->addDocHeaderCloseAndSaveButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'vendor',
        );

    	return $view->renderResponse('VendorEdit');
    }


    final function delete(
        ServerRequestInterface $request,
    ): ResponseInterface {
//        ModuleController::debugRequest($request, 'Extension - Delete');
        $bodyParams = array_merge($request->getParsedBody() ?? [],$request->getQueryParams() ?? []);

        $vendorName = $bodyParams['vendorName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

        self::deleteVendor($parsedBody['vendorName'] ?? '');

        $view->assignMultiple([
            'vendorList' => $this->vendors,
        ]);

        $this->addDocHeaderModuleDropDown(
            $view,
            $this->uriBuilder,
            'vendor',
        );
        $this->addDocHeaderCloseButtons(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'extension',
        );
        $this->addDocHeaderAddButton(
            $view,
            $this->iconFactory,
            $this->uriBuilder,
            'locallang.xlf:function.vendor.add.h',
		    'vendor.add',
        );

    	return $view->renderResponse('VendorList');
    }


    // ------------------------------------------------------------------

    /**
     *
     */
    final function writeVendors(): void
    {
// ToDO
        $path = Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder();

        $fileName =
            Tools\ExtensionbuilderFolder::getExtensionBuilderFolder()
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $vendorName.DIRECTORY_SEPARATOR
            . 'vendor.json';
//        Tools\Json::write($fileName, $developer);
	}


    private function saveVendorXXX(
        string $vendorName,
    ): void {

        $vendorData = $this->vendors[$vendorName] ?? [];
        $vendorData['vendorName'] = $vendorName;

        // Einmal trimmen bitte
	    foreach ($vendorData ?? [] as $vendorField) {
	        if (is_string($vendorField)) {
	            $vendorField = trim($vendorField);
	        }
	    }

        $this->writeVendors(); // 1234

        $this->flashMessage('', 'Saving vendor: ' . $vendorName);
    }

    private function deleteVendorXXX(
        string $vendorName,
    ): void {
        //
        // ToDo - Daten in Backup verschieben
        //
        $this->flashMessage('ToDo', 'Delete vendor: ' . $vendorName);
	}


    private function copyVendorXXX(
        string $vendorName,
        string $vendorNameNew,
    ): void {
        //
        // ToDo - Es müssen auch alle Vendor Eintrage in den Extension geändert werden 
        //
        $this->flashMessage('ToDo', 'Copy vendor: ' . $vendorName . ' to ' . $vendorNameNew);
	}

}