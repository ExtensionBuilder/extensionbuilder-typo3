<?php
declare(strict_types = 1);

// ToDO Tools\RestApiClient::github($extension);
// ToDO Tools\RestApiClient::packagist($extension);
// ToDO Build Counter

namespace ExtensionBuilder\ExtensionbuilderTypo3;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use ExtensionBuilder\ExtensionbuilderTypo3\ManageExtension;

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use ExtensionBuilder\ExtensionbuilderTypo3Core\BuildExtensionCore;

class BuildExtension extends ManageExtension
{

    private bool $buildLogUsage0 = false;
    private bool $buildLogUsage = false;
    private bool $buildLogInfo = false;
    private bool $buildLogWarning = false;
    private bool $buildLogToDo = false;
    private bool $buildLogError = false;

    public function build(
        string $vendorName,
        string $extensionName,
		string $builderUri,
        bool $copyInExtension = true,
        bool $clearCache = false,
        bool $dumpAutoload = false,
    ): void {

        $clearCache = true;
        $dumpAutoload = true;

		$buildOk = false;
		if ($_SERVER['SERVER_NAME'] === 'development.extension-builder.dev') {
            $buildOk = self::buildLocal($vendorName, $extensionName);
//            $buildOk = self::buildRemote($vendorName, $extensionName, 'https://development.extension-builder.dev/');
//            $buildOk = self::buildRemote($vendorName, $extensionName, 'https://typo3.extension-builder.dev/');
        } else {
            $buildOk = self::buildRemote($vendorName, $extensionName, $builderUri);
        }


        // Extesion installieren (kopieren)
        if ($buildOk && $copyInExtension) {
            if (!Environment::isComposerMode()) {

                $buildPath =
                    Environment::getVarPath() . DIRECTORY_SEPARATOR
                    . Setup\GlobalConfig::VAR_EB
                    . $vendorName . DIRECTORY_SEPARATOR
                    . $extensionName . DIRECTORY_SEPARATOR
                    . 'build' . DIRECTORY_SEPARATOR;

                $extPath =
                    Environment::getPublicPath() . DIRECTORY_SEPARATOR
                    . 'typo3conf' . DIRECTORY_SEPARATOR
                    . 'ext' . DIRECTORY_SEPARATOR
                    . $extensionName . DIRECTORY_SEPARATOR;

                GeneralUtility::rmdir($extPath, true);
                GeneralUtility::mkdir_deep($extPath);
                GeneralUtility::copyDirectory($buildPath, $extPath);

                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    '<DocumentRoot>/typo3conf/ext/' . $extensionName,
                    'Successfully copy extensions files.',
                    ContextualFeedbackSeverity::OK,
                );
                $notificationQueue->enqueue($flashMessage);

			} else {

//ToDo Ext / Composer Version

			}
		}

        if ($clearCache) {
            $clearCacheService = GeneralUtility::makeInstance('TYPO3\\CMS\\Install\\Service\\ClearCacheService');
            $clearCacheService->clearAll();

            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                '',
                'Successfully cleared all caches and all available opcode caches.',
                ContextualFeedbackSeverity::OK,
            );
            $notificationQueue->enqueue($flashMessage);
		}

        if ($dumpAutoload) {
            if (!Environment::isComposerMode()) {

                ClassLoadingInformation::dumpClassLoadingInformation();

                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    '',
                    'Successfully dumped class loading information for extensions.',
                    ContextualFeedbackSeverity::OK,
                );
                $notificationQueue->enqueue($flashMessage);
            }
		}

	}


    private function buildLocal(
        string $vendorName,
        string $extensionName,
    ): bool {
        $tmpReturn = true;

        $multipar = self::buildRequest($vendorName, $extensionName);

        $sourcePath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\GlobalConfig::VAR_EB
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'source' . DIRECTORY_SEPARATOR;

        $sourcePathCore =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\GlobalConfig::VAR_EB_CORE
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'source' . DIRECTORY_SEPARATOR;

        GeneralUtility::rmdir($sourcePathCore, true);
        GeneralUtility::mkdir_deep($sourcePathCore);
        GeneralUtility::copyDirectory($sourcePath, $sourcePathCore);

		$extConf = Tools\ExtensionConfiguration::read($sourcePathCore);

        // Erzeuge Extesnsion
// ToDo Zeitmesseung
        $buildCore = new BuildExtensionCore($extConf);
        $buildCore->build();
		
        $buildPathCore =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\GlobalConfig::VAR_EB_CORE
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'build' . DIRECTORY_SEPARATOR;

        $buildPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\GlobalConfig::VAR_EB
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'build' . DIRECTORY_SEPARATOR;

        GeneralUtility::copyDirectory($buildPathCore, $buildPath);

        $debugPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\GlobalConfig::VAR_EB
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'debug' . DIRECTORY_SEPARATOR;

        // Log in Json-Date schreiben
		GeneralUtility::mkdir_deep($debugPath);
        file_put_contents(
            $debugPath.'bildLog.json',
            json_encode(($buildCore->extConf['buildLog'] ?? []), JSON_PRETTY_PRINT),
        );

        if (($buildCore->buildLog['Usage'] ?? false) && $this->buildLogUsage0) {
            debug(
                $usageX = \ExtensionBuilder\ExtensionbuilderTypo3\Tools\ConfigArray::removeUsage($buildCore->buildLog['Usage']),
                'Build no usage'
            );
        }

        if (($buildCore->buildLog['Usage'] ?? false) && $this->buildLogUsage) {
            debug($buildCore->buildLog['Usage'], 'Build usage');
        }
        if (($buildCore->buildLog['Info'] ?? false) && $this->buildLogInfo) {
            debug($buildCore->buildLog['Info'], 'Build info');
        }
        if (($buildCore->buildLog['Warning'] ?? false) && $this->buildLogWarning) {
            debug($buildCore->buildLog['Warning'], 'Build warning');
        }
        if (($buildCore->buildLog['ToDo'] ?? false) && $this->buildLogToDo) {
            debug($buildCore->buildLog['ToDo'], 'Build todo');
        }
        if (($buildCore->buildLog['Error'] ?? false) && $this->buildLogError) {
            debug($buildCore->buildLog['Error'], 'Build error');
        }

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            'Extension: '.$extensionName,
            'Extension is build (local).',
            ContextualFeedbackSeverity::OK,
        );
        $notificationQueue->enqueue($flashMessage);

        return $tmpReturn;
	}

    private function buildRemote(
        string $vendorName,
        string $extensionName,
        string $builderUri,
    ): bool {
        $return = false;

        $multipart = self::buildRequest($vendorName, $extensionName);

        // Extenssion Daten zum erstellen an den Web-Service senden		
        $resultCode = Tools\RestApiClient::build(
           $builderUri,
           $multipart,
        );

        if ($resultCode ?? false) {
            $debugPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . Setup\GlobalConfig::VAR_EB
                . $vendorName . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR
                . 'debug' . DIRECTORY_SEPARATOR;
            file_put_contents(
                $debugPath.'response.json',
                json_encode($resultCode, JSON_PRETTY_PRINT)
            );
            $importPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . Setup\GlobalConfig::VAR_EB
                . $vendorName . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR
                . 'import' . DIRECTORY_SEPARATOR;
            $buildPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . Setup\GlobalConfig::VAR_EB
                . $vendorName . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR
                . 'build'.DIRECTORY_SEPARATOR;
            $extension = $resultCode['extension'];
            $zipName = $resultCode['zipName'] ?? $extension.'.zip';
            $base64Zip = $resultCode['base64Zip'];
            $base64Zip = str_replace('data:image/zip;base64,', '', $base64Zip);
            $base64Zip = str_replace(' ', '+', $base64Zip);

            file_put_contents($importPath.$zipName, base64_decode($base64Zip));

            $unzipFile = $importPath.$zipName;

            GeneralUtility::mkdir_deep($buildPath);
            Tools\ZipArchive::unzip($unzipFile, $buildPath);

            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                'Extension: ' . $extensionName,
                'Extension is build (remote).',
                ContextualFeedbackSeverity::OK,
            );
            $notificationQueue->enqueue($flashMessage);

            $return = true;
        }
        return $return;
	}

    private function buildRequest(
        string $vendorName,
        string $extensionName,
    ): array {
        $extensionPath = self::buildSetup($vendorName, $extensionName);
		$extensionSourcePath = $extensionPath . 'source' . DIRECTORY_SEPARATOR;
		$extensionDebugPath = $extensionPath . 'debug' . DIRECTORY_SEPARATOR;

        $tmpVersion = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion(Setup\GlobalConfig::EXT_NAME);

        $multipart = [];
		$multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'version', 'contents' => $tmpVersion ?? '0.0.0'];
        $multipart['multipart'][] = ['name' => 'apikey', 'contents' => 'community'];
        $multipart['multipart'][] = ['name' => 'vendorhash', 'contents' => ''];
        $multipart['multipart'][] = ['name' => 'vendor', 'contents' => $vendorName];
        $multipart['multipart'][] = ['name' => 'extension',  'contents' => $extensionName];

        $dependentExtensionsList['dependenciesExport'] = $this->foreignExtensionsList;
        Tools\Json::write(
            $extensionSourcePath . 'extension.dependencies.export.json',
            $dependentExtensionsList
        );
	
		$jsonFileList = GeneralUtility::getFilesInDir($extensionSourcePath, 'json');

        // Build multipart value for JSON-Files
        foreach ($jsonFileList ?? [] as $jsonFile) {
			$base64 = 'data:text/plain;base64,' . base64_encode(file_get_contents($extensionSourcePath . $jsonFile));
            $multipart['multipart'][] = ['name' => $jsonFile, 'contents' => $base64];
        }


        // Build multipart value for DeveloperCode-Flies
        $extensionDevCodePath = $extensionSourcePath . 'DeveloperCode';
		if (file_exists($extensionDevCodePath)) {

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extensionDevCodePath),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($files as $fileName => $fileData) {
                $fileName = str_replace('\\', '/', $fileName);
                $pos = strpos ($fileName, 'DeveloperCode');
				if ($pos > 0) {
                    if (is_file($fileName)) {
                     $fileNameNew = substr ($fileName, $pos);
                     $base64 = 'data:text/plain;base64,' . base64_encode(file_get_contents($fileName));
                     $multipart['multipart'][] = ['name' => $fileNameNew, 'contents' => $base64];
    				}
				}
	    	}
		}

        file_put_contents(
            $extensionDebugPath . 'request.json',
            json_encode($multipart, JSON_PRETTY_PRINT),
        );

        return $multipart;
	}

    private function buildSetup(
        string $vendorName,
        string $extensionName,
    ): string {
        $buildPath = Environment::getVarPath().DIRECTORY_SEPARATOR . Setup\GlobalConfig::VAR_EB;
        $vendorPath = $buildPath . $vendorName . DIRECTORY_SEPARATOR;
        $extensionPath = $vendorPath . $extensionName . DIRECTORY_SEPARATOR;

        // Clean up
        GeneralUtility::rmdir($extensionPath, true);

        $extensionSourcePath = $extensionPath . 'source' . DIRECTORY_SEPARATOR;
        $extensionDebugPath = $extensionPath . 'debug' . DIRECTORY_SEPARATOR;
        $extensionImportPath = $extensionPath . 'import' . DIRECTORY_SEPARATOR;
        $extensionBuilPath = $extensionPath . 'build' . DIRECTORY_SEPARATOR;

        GeneralUtility::mkdir_deep($extensionSourcePath);
        GeneralUtility::mkdir_deep($extensionDebugPath);
        GeneralUtility::mkdir_deep($extensionImportPath);
        GeneralUtility::mkdir_deep($extensionBuilPath);

        $extensionbuilderDevPath =
            Environment::getPublicPath() . DIRECTORY_SEPARATOR
            . 'fileadmin' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR;

        $extensionDevSourcePath =
            $extensionbuilderDevPath
            . $vendorName . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR;
	
        GeneralUtility::copyDirectory($extensionDevSourcePath, $extensionSourcePath);
        GeneralUtility::rmdir($extensionSourcePath . 'build', true);

        // Copyback ToDo 
        $extConf = Tools\ExtensionConfiguration::read($extensionDevSourcePath);
        if ($extConf['extensionBuild']['copyBack'] ?? false) {
            $extensionName = $extConf['extension']['extensionName'];
            $extPath =
                Environment::getPublicPath() . DIRECTORY_SEPARATOR
                . 'typo3conf' . DIRECTORY_SEPARATOR
                . 'ext' . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR;
            $developerCodePath = $extensionDevSourcePath . 'DeveloperCode' . DIRECTORY_SEPARATOR;
            foreach ($extConf['extensionBuild']['copyBack'] ?? [] as $copyBackName => $copyBackData) {
                if ($copyBackData) {
                    $copyBackSorce = $extPath . $copyBackName;
                    $copyBackDestination = $developerCodePath . $copyBackName;
                    if (is_dir($copyBackSorce)) {
                        GeneralUtility::copyDirectory($copyBackSorce, $copyBackDestination);
    				} elseif (is_file($copyBackSorce)) {
                        $path_parts = pathinfo($copyBackDestination);
                        GeneralUtility::mkdir_deep($path_parts['dirname']);
                        copy($copyBackSorce, $copyBackDestination);
    				}
				}
		    }
        }

        return $extensionPath;
    }

}