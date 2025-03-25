<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;


//                'enum': 100,
//                'language': {
//                    'en': 'Netto',
//                    'de': 'Netto'
//                }

#[AsController]
final class EnumerationConstantController extends ExtensionBuilderController
{

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';



//        ModuleController::addDocHeaderModuleDropDown(
//            $view,
//            $this->uriBuilder,
//            'extension',
//        );
		
        if (in_array($parsedBody['cmd'] ?? [], ['save',], true)) {

    		$extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

            $tmpNode = 'enumerations';

    		$tmpName = strtolower($parsedBody['enumerationName']);
    		$tmpNameUc = ucfirst($tmpName);
            $tmpName = strtoupper($tmpName);

    		$tmpData = [];
    		$tmpData[$tmpNode] = [];
    		$tmpData[$tmpNode][$tmpName] = [];

    		if ($parsedBody['enumerationData']['description'] ?? false) {
    		    $tmpData[$tmpNode][$tmpName]['description'] = $parsedBody['enumerationData']['description'];
    		}

    		Tools\ConfigArray::arrayMerge($extensionData,$tmpData);


//            self::save(
//                $vendorName ?? '',
//                $extensionName ?? '',
//                $extensionData ?? []
//            );

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'extensionData' => $extensionData,
		    	'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            ModuleController::addDocHeaderCloseAndSaveButtons(
                'extension.enumeration.edit',
            );
			
            return $view->renderResponse('ExtensionEnumerationAdd');
        } else {

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName
            ]);
            ModuleController::addDocHeaderCloseAndSaveButtons(
                'extension.enumeration.edit',
                $vendorName,
                $extensionName
            );
        	return $view->renderResponse('ExtensionEnumerationConstantAdd');
        }
    }

    public function edit(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

//        ModuleController::addDocHeaderModuleDropDown(
//            $view,
//            $this->uriBuilder,
//            'extension'
//        );

        $enumerationName = $request->getQueryParams()['enumerationName'] ?? '';
        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $enumerationData = $extensionData['enumerations'][$enumerationName];

        if (in_array($parsedBody['cmd'] ?? [], ['save',], true)) {
            Tools\ConfigArray::arrayMerge($extensionData,$enumerationData);

            self::save(
                $parsedBody['vendorName'] ?? '',
                $parsedBody['extensionName'] ?? '',
                $extensionData ?? []
            );
            ModuleController::addDocHeaderAddButton(
                'locallang.xlf:function.extension.add.h',
			    'extension.add'
            );
            $view->assignMultiple([
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'extensionData' => $extensionData,
                'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);
            return $view->renderResponse('ExtensionEnumerationEdit');
        } else {
            $enumerationData = $extensionData['enumerations'][$enumerationName];

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'enumerationName' => $enumerationName,
                'enumerationData' => $enumerationData,
            ]);
            ModuleController::addDocHeaderCloseAndSaveButtons(
                'extension.enumeration.edit',
                $vendorName,
                $extensionName,
            );
            return $view->renderResponse('ExtensionEnumerationConstantEdit');
		}
    }

    public function delete(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $vendorName = $queryParams['vendorName'] ?? $parsedBody['vendorName'] ?? '';
        $extensionName = $queryParams['extensionName'] ?? $parsedBody['extensionName'] ?? '';
        $enumerationName = $queryParams['enumerationName'] ?? '';
        $constantName = $queryParams['constantName'] ?? '';

		$view = $this->moduleTemplateFactory->create($request);

//        ModuleController::addDocHeaderModuleDropDown(
//            'extension',
//        );

        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
//		unset( $extensionData['tables'][$tableName] );



//        self::save(
//            $vendorName ?? '',
//            $extensionName ?? '',
//            $extensionData ?? [],
//        );

        $view->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'enumerationName' => $enumerationName,
        ]);
        ModuleController::addDocHeaderCloseAndSaveButtons(
            'extension.edit',
            $vendorName,
            $extensionName,
        );
        return $view->renderResponse('ExtensionEnumerationEdit');
    }

    // ------------------------------------------------------------------

    protected function save(
        string $vendorName,
        string $extensionName,
        array $extensionData,
    ): void {

        $extensionData['extension']['version']  = (string)($extensionData['extension']['versionMajor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionMinor'] ?? '0');
        $extensionData['extension']['version'] .= '.';
        $extensionData['extension']['version'] .= (string)($extensionData['extension']['versionRevision'] ?? '0');

        $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] = $extensionData;
        $this->extensionbulderObject->write($vendorName, $extensionName);
        ModuleController::flashMessage('Vendor: '.$vendorName, 'Saving extension: ' . $extensionName);
    }

}