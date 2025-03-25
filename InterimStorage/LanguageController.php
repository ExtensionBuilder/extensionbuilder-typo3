<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

#[AsController]
final class LanguageController extends ExtensionBuilderController
{

// ToDo

    public array $configuration = [];
    public array $projects = [];

    public function addAction(): ResponseInterface {
        $bodyParams = array_merge(($request->getParsedBody() ?? []), $request->getQueryParams() ?? []);
		$view = $this->moduleTemplateFactory->create($request);

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

        $view->assign(
            'dateFormat',
            [
                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
            ]
        );

        $this->addDocHeaderModuleDropDown(
            $view,
            'extension',
        );

        if ($parsedBody['extensionData'] ?? false) {
            $vendorName = $parsedBody['extensionData']['extension']['vendorName'];
            $extensionName = $parsedBody['extensionData']['extension']['extensionName'];
        } else {
            $vendorName = $request->getQueryParams()['vendorName'] ?? '';
            $extensionName = $request->getQueryParams()['extensionName'] ?? '';
		}
		
        if (in_array($parsedBody['action'] ?? [], ['save',], true)) {
    		$extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];

    		$tableName = strtolower($parsedBody['tableName']);
    		$tableNameUc = ucfirst($tableName);

    		$tableData = [];
    		$tableData['tables'] = [];
    		$tableData['tables'][$tableName] = [];
    		$tableData['tables'][$tableName]['makeSql'] = true;
    		$tableData['tables'][$tableName]['makeModel'] = true;
    		$tableData['tables'][$tableName]['makeTca'] = true;
    		$tableData['tables'][$tableName]['makeFluid'] = true;
    		$tableData['tables'][$tableName]['language'] = [];
    		$tableData['tables'][$tableName]['language']['en'] = $tableNameUc;
    		if ($parsedBody['tableData']['description'] ?? false) {
    		    $tableData['tables'][$tableName]['description'] = $parsedBody['tableData']['description'];
    		}
    		Tools\ConfigArray::arrayMerge($extensionData,$tableData);
            self::save(
                $vendorName ?? '',
                $extensionName ?? '',
                $extensionData ?? [],
            );

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'extensionData' => $extensionData,
		    	'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            $this->addDocHeaderCloseAndSaveButtons(
                $view,
                'extension.edit',
            );
			
            return $view->renderResponse('ExtensionEdit');
        } else {

            $this->addDocHeaderCloseAndSaveButtons(
                $view,
                'extension.edit',
                $vendorName,
                $extensionName,
            );

		    $extensionData = [];
		    $extensionData['extension'] = [];
		    $extensionData['extension']['vewndorName'] = '';
		    $extensionData['extension']['extensionName'] = '';
		    $extensionData['extension']['description'] = '';

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName
            ]);

        	return $view->renderResponse('ExtensionEnumerationAdd');
        }
    }

    public function editAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

		$view = $this->moduleTemplateFactory->create($request);
        $view->assign(
            'dateFormat',
            [
                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
            ]
        );

        $this->addDocHeaderModuleDropDown(
            $view,
            'extension',
        );

        if ($parsedBody['extensionData'] ?? false) {
            $vendorName = $parsedBody['extensionData']['extension']['vendorName'];
            $extensionName = $parsedBody['extensionData']['extension']['extensionName'];
        } else {
            $vendorName = $request->getQueryParams()['vendorName'] ?? '';
            $extensionName = $request->getQueryParams()['extensionName'] ?? '';
		}
        $enumerationName = $request->getQueryParams()['enumerationName'] ?? '';
        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
        $enumerationData = $extensionData['enumerations'][$enumerationName];

        if (in_array($parsedBody['action'] ?? [], ['save',], true)) {
            Tools\ConfigArray::arrayMerge($extensionData,$enumerationData);

            self::save(
                $parsedBody['vendorName'] ?? '',
                $parsedBody['extensionName'] ?? '',
                $extensionData ?? [],
            );

            $this->addDocHeaderAddButton(
                $view,
                'locallang.xlf:function.extension.add.h',
			    'extension.add',
            );

            $view->assignMultiple([
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'extensionData' => $extensionData,
                'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
            ]);

            return $view->renderResponse('ExtensionEdit');
        } else {
            $enumerationData = $extensionData['enumerations'][$enumerationName];
			
            $this->addDocHeaderCloseAndSaveButtons(
                $view,
                'extension.edit',
                $vendorName,
                $extensionName,
            );

            $view->assignMultiple([
                'vendorName' => $vendorName,
    			'extensionName' => $extensionName,
                'enumerationName' => $enumerationName,
                'enumerationData' => $enumerationData,
            ]);			

            return $view->renderResponse('ExtensionEnumerationEdit');
		}
    }

    public function deleteAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getParsedBody() ?? [], $this->request->getQueryParams() ?? []);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $languageService = $GLOBALS['LANG'];
		$parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];

		$view = $this->moduleTemplateFactory->create($request);
        $view->assign(
            'dateFormat',
            [
                'day'  => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y',
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']   ?? 'H:i',
            ]
        );

        $this->addDocHeaderModuleDropDown(
            'extension',
        );

        if ($parsedBody['extensionData'] ?? false) {
            $vendorName = $parsedBody['extensionData']['extension']['vendorName'];
            $extensionName = $parsedBody['extensionData']['extension']['extensionName'];
        } else {
            $vendorName = $request->getQueryParams()['vendorName'] ?? '';
            $extensionName = $request->getQueryParams()['extensionName'] ?? '';
		}

        $tableName = $request->getQueryParams()['tableName'] ?? '';
        $extensionData = $this->extensionbulderObject->vendorsAndExtensions[$vendorName]['extensions'][$extensionName];
		unset($extensionData['tables'][$tableName]);

        self::save(
            $vendorName ?? '',
            $extensionName ?? '',
            $extensionData ?? [],
        );

        $this->addDocHeaderAddButton(
            'locallang.xlf:function.extension.add.h1',
			'extension.add',
        );

        $view->assignMultiple([
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'extensionData' => $extensionData,
            'registeredVendorGroups' => $this->getRegisteredVendorGroups(),
        ]);

        return $view->renderResponse('ExtensionEdit');
    }

    // ------------------------------------------------------------------

    final function save(
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
        ModuleController::flashMessage('Vendor: ' . $vendorName, 'Saving extension: ' . $extensionName);
    }


}