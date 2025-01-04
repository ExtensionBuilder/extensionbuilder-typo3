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

    private string $vendorName = '';
    private string $extensionName = '';

    public function build(
        string $vendorName,
        string $extensionName,
		string $builderUri,
        bool $copyInExtension = true,
        bool $flushT3andPhpCache = false,
        bool $analyzeDatabaseStructure = false,
        bool $rebuildPhpAutoload = false,
    ): void {

        $this->vendorName = $vendorName;
        $this->extensionName = $extensionName;		

		$buildOk = false;

		if ($_SERVER['SERVER_NAME'] === 'development.extension-builder.dev') {
            $buildOk = self::buildLocal();
//            $buildOk = self::buildRemote($builderUri);
//            $buildOk = self::buildRemote('https://development.extension-builder.dev/');
        } else {
            $buildOk = self::buildRemote($builderUri);
        }


        // Extesion installieren (kopieren)
        if ($buildOk && $copyInExtension) {
            if (!Environment::isComposerMode()) {

                $buildPath =
                    Environment::getVarPath() . DIRECTORY_SEPARATOR
                    . Setup\Config::VAR_EB
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'build' . DIRECTORY_SEPARATOR;

                $extPath =
                    Environment::getPublicPath() . DIRECTORY_SEPARATOR
                    . 'typo3conf' . DIRECTORY_SEPARATOR
                    . 'ext' . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR;

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

// ToDo
//        bool $flushT3andPhpCache = false,
//        bool $analyzeDatabaseStructure = false,
//        bool $rebuildPhpAutoload = false,

        $clearCache = true;
        $dumpAutoload = true;

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

//8888
    private function buildLocal(): bool
    {

// ToDo Zeitmesseung

        $tmpReturn = true;

        $multipar = self::buildRequest();

        $buildCore = new BuildExtensionCore($this->vendorName, $this->extensionName);

        $sourcePath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\Config::VAR_EB
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR
            . 'source' . DIRECTORY_SEPARATOR;

        $sourcePathCore = $buildCore->pathes['source'];
		
        GeneralUtility::rmdir($sourcePathCore, true);
        GeneralUtility::mkdir_deep($sourcePathCore);
        GeneralUtility::copyDirectory($sourcePath, $sourcePathCore);

		$extConf = Tools\ExtensionConfiguration::read($sourcePathCore);

        // Erzeuge Extesnsion
        $buildStart = microtime(true);
        $buildCore->build($extConf);
        $buildDuration = microtime(true) - $buildStart;

        $buildPathCore = $buildCore->pathes['build'];
		
        $buildPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\Config::VAR_EB
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR
            . 'build' . DIRECTORY_SEPARATOR;

        GeneralUtility::copyDirectory($buildPathCore, $buildPath);

        $debugPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\Config::VAR_EB
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR
            . 'debug' . DIRECTORY_SEPARATOR;

        // Log in Json-Date schreiben
		GeneralUtility::mkdir_deep($debugPath);
        file_put_contents(
            $debugPath . 'bildLog.json',
            json_encode(($buildCore->extConf['buildLog'] ?? []), JSON_PRETTY_PRINT),
        );

        if (($buildCore->buildLog['Usage'] ?? false) && $this->buildLogUsage0) {
// ToDo
            debug(
                $usageX = \ExtensionBuilder\ExtensionbuilderTypo3\Tools\ConfigArray::removeUsage($buildCore->buildLog['Usage']),
                'Build no usage'
            );
        }

        if (($buildCore->buildLog['Usage'] ?? false) && $this->buildLogUsage) {
// ToDo
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
            'Extension: ' . $this->extensionName,
            'Extension is build (local).',
            ContextualFeedbackSeverity::OK,
        );
        $notificationQueue->enqueue($flashMessage);

        return $tmpReturn;
	}


    private function buildRemote(
        string $builderUri,
    ): bool {
        $return = false;

        $multipart = self::buildRequest();

        $resultCode = Tools\RestApiClient::build(
           $builderUri,
           $multipart,
        );

        if ($resultCode ?? false) {

            $debugPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . Setup\Config::VAR_EB
                . $this->vendorName . DIRECTORY_SEPARATOR
                . $this->extensionName . DIRECTORY_SEPARATOR
                . 'debug' . DIRECTORY_SEPARATOR;
            file_put_contents(
                $debugPath . 'response.json',
                json_encode($resultCode, JSON_PRETTY_PRINT)
            );
            $importPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . Setup\Config::VAR_EB
                . $this->vendorName . DIRECTORY_SEPARATOR
                . $this->extensionName . DIRECTORY_SEPARATOR
                . 'import' . DIRECTORY_SEPARATOR;
            $buildPath =
                Environment::getVarPath() . DIRECTORY_SEPARATOR
                . Setup\Config::VAR_EB
                . $this->vendorName . DIRECTORY_SEPARATOR
                . $this->extensionName . DIRECTORY_SEPARATOR
                . 'build'.DIRECTORY_SEPARATOR;

            $extension = $resultCode['extension'];
            $zipName = $resultCode['zipName'] ?? $extension . '.zip';
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
                'Extension: ' . $this->extensionName,
                'Extension is build (remote).',
                ContextualFeedbackSeverity::OK,
            );
            $notificationQueue->enqueue($flashMessage);

            $return = true;
        }
        return $return;
	}


    private function buildRequest(): array
    {
        $extensionPath = self::buildSetup();
		$extensionSourcePath = $extensionPath . 'source' . DIRECTORY_SEPARATOR;
		$extensionDebugPath = $extensionPath . 'debug' . DIRECTORY_SEPARATOR;

        $version = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('extensionbuilder_typo3');

        $multipart = [];
		$multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'build'];
        $multipart['multipart'][] = ['name' => 'version', 'contents' => $version ?? '0.0.0'];

        $multipart['multipart'][] = ['name' => 'systemId', 'contents' => 'community'];
        $multipart['multipart'][] = ['name' => 'systemIp', 'contents' => 'community'];
        $multipart['multipart'][] = ['name' => 'systemMac', 'contents' => 'community'];
        $multipart['multipart'][] = ['name' => 'apikey', 'contents' => 'community'];

        $multipart['multipart'][] = ['name' => 'vendorhash', 'contents' => ''];
        $multipart['multipart'][] = ['name' => 'vendor', 'contents' => $this->vendorName];
        $multipart['multipart'][] = ['name' => 'extension',  'contents' => $this->extensionName];

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


    private function buildSetup(): string
    {
        $buildPath = Environment::getVarPath().DIRECTORY_SEPARATOR . Setup\Config::VAR_EB;
        $vendorPath = $buildPath . $this->vendorName . DIRECTORY_SEPARATOR;
        $extensionPath = $vendorPath . $this->extensionName . DIRECTORY_SEPARATOR;

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
            . $this->vendorName . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR;
	
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