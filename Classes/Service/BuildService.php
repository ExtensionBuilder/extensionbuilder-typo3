<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Service;
	
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Package\PackageManager;

use Psr\Log\LoggerInterface;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */
class BuildService
{

    /**
     * @since 0.12
     */
    public function __construct(
        private readonly PackageManager $packageManager,
        private readonly LoggerInterface $logger,
        public array $ownerInfo = [],
        public bool $isComposerMode = false,
        public array $coreStatus = [],
        public string $beUserId = '',
        public bool $beUserIsAdmin = false,
        public string $beUserGroup = '',
        public string $projectPath = '',
        public string $repositoryPath = '',
        public string $extensionPath = '',
        public string $configurationData = '',
		public string $t3tmpPath = '',
        public string $t3tmpCorePath = '',
        public string $buildPath = '',
        public bool $builderLocal = false,
        public string $builderLocalVersion = '',
        public string $builderVersion = '',
        public string $vendorName = '',
        public string $extensionName = '',
        public array $configuration = [],
        public array $developers = [],
        public array $developer = [],
        public bool $noDeveloper = true,
        public array $vendors = [],
        public bool $noVendors = true,
        public array $extension = [],
        public array $vendorsAndExtensions = [],
        public string $buildResult = '',
    // Cache Test
    private array $extensionCache = [],

    ) {
        if (PHP_SAPI === 'cli') {
            $this->ownerInfo = posix_getpwuid(fileowner(getcwd()));
		} else {
            $this->ownerInfo = posix_getpwuid(fileowner($_SERVER['DOCUMENT_ROOT']));
		}

        $this->isComposerMode = Environment::isComposerMode();

		$this->projectPath = Environment::getProjectPath() . DIRECTORY_SEPARATOR;

        $extensionbuilderPath = 'ExtensionBuilder';
        if (!$this->isComposerMode) {
            $extensionbuilderPath = '.' . $extensionbuilderPath;
        }

		$this->repositoryPath = $this->projectPath . $extensionbuilderPath . DIRECTORY_SEPARATOR;
        $this->configurationData = $this->repositoryPath . 'configuration.json';

		$this->t3tmpPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR . 'ExtensionBuilder' . DIRECTORY_SEPARATOR;
		$this->t3tmpCorePath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR . 'ExtensionBuilderCore' . DIRECTORY_SEPARATOR;

		$this->buildPath = $this->t3tmpPath;

        $extConfigurationPath =
            $this->packageManager->getPackage('extensionbuilder_typo3')->getPackagePath()
            . 'Configuration' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR;

        if ($this->builderLocal = $this->packageManager->isPackageActive('extensionbuilder_typo3_core')) {
            $this->builderLocalVersion = 
                $this->packageManager->
                getPackage('extensionbuilder_typo3_core')->
                getPackageMetaData()->
                getVersion();
        }

        $this->builderVersion = 
            $this->packageManager->
            getPackage('extensionbuilder_typo3')->
            getPackageMetaData()->
            getVersion();

        self::readConfiguration();

        self::readDevelopers();

        if (!(PHP_SAPI === 'cli')) {
            self::readDeveloper();
        }

        self::readVendors();

        self::readVendorsAndExtensions();

        if (
            ($this->configuration['typo3']['builderUrl'] ?? false ) &&
            ($this->configuration['typo3']['builderApi'] ?? false )

        ) {
            $this->coreStatus = Tools\RestApiClient::getStatus(
                $this->configuration['typo3']['builderUrl'],
                $this->configuration['typo3']['builderApi'],
	        );
        } else {
	        $this->coreStatus['status'] = 'no config';
	        $this->coreStatus['version'] = '';
	        $this->coreStatus['serverUrl'] = '';
	        $this->coreStatus['remoteIp'] = '';
	        $this->coreStatus['maintenanceTime'] = '';
        }

        if ($this->isComposerMode) {
            $this->extensionPath =
                Environment::getProjectPath() . DIRECTORY_SEPARATOR
                . $this->configuration['typo3']['composerRepository'] . DIRECTORY_SEPARATOR;
	    } else {
            $this->extensionPath =
                Environment::getPublicPath() . DIRECTORY_SEPARATOR
                . 'typo3conf' . DIRECTORY_SEPARATOR
                . 'ext' . DIRECTORY_SEPARATOR;
        }

    }


    // Configuration

    /**
     * @since 0.12
     */
    public function readConfiguration(): void
    {
        $this->configuration = [];

        if (file_exists($this->configurationData)) {
            $configurationJson = Tools\Json::read($this->configurationData);
            $this->configuration = $configurationJson['configuration'] ?? [];
            return;
        }

        if (!is_dir($this->repositoryPath)) {
            GeneralUtility::mkdir_deep($this->repositoryPath);
        }

	}

    /**
     * @since 0.12
     */
    public function readDevelopers(): void
    {
        $developers = Tools\Folder::scanForFile($this->repositoryPath, 'json', 'developer.');

        foreach($developers  ?? [] as $developerKey => $developerValue) {
            $fileName = $this->repositoryPath . $developerKey . '.json';
            $developerJson = Tools\Json::read($fileName);
            $this->developers[substr($developerKey, strlen('developer.'))] = $developerJson['developer'] ?? [];
        }
	}

    /**
     * @since 0.12
     */
    public function readDeveloper(): void
    {
        if ((PHP_SAPI === 'cli')) {
            $this->beUserId = 'cli';
    		$this->beUserIsAdmin = 1;
    		$this->beUserGroup = '';
		} else {
            $this->beUserId = $GLOBALS['BE_USER']->user['username'];
            $this->beUserIsAdmin = (bool)$GLOBALS['BE_USER']->user['admin'];
            $this->beUserGroup = $GLOBALS['BE_USER']->user['usergroup'] ?? '';

            $fileName = $this->repositoryPath . 'developer.' . $this->beUserId . '.json';

            if (file_exists($fileName)) {
                $developerJson = Tools\Json::read($fileName);
                $this->developer = $developerJson['developer'] ?? [];
                $this->noDeveloper = false;
	    	}
        }
    }

    /**
     * @since 0.12
     */
    public function readVendors(): void
    {
        $vendorList = Tools\Folder::scanForDirectory($this->repositoryPath);

        foreach($vendorList ?? [] as $vendorName) {
            $fileName =
                $this->repositoryPath
                . $vendorName . DIRECTORY_SEPARATOR
                . 'vendor.json';

            if (file_exists($fileName)) {
                $this->noVendors = false;

                $vendor = Tools\Json::read($fileName);
                $this->vendors[$vendorName] = [];
                $this->vendors[$vendorName] = $vendor['vendor'] ?? [];
            }
		}
	}


    /**
     * @since 0.12
     */
    public function readExtension(
        string $vendorName,
        string $extensionName,
    ): void {
        $cacheKey = $vendorName . '/' . $extensionName;

        if (isset($this->extensionCache[$cacheKey])) {
            $this->extension = $this->extensionCache[$cacheKey];
            return;
        }

        $this->extension = [];

        $extensionPath =
            $this->repositoryPath
            . $vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR;

        $this->extension = self::readExtensionHelper($extensionPath);

        $this->extensionCache[$cacheKey] = $this->extension;
	}

    /**
     * @since 0.12
     */
    private function readExtensionHelper(
        string $pathToExtensionConfiguration,
    ): array {
        $return = [];
        $extensionJsonList = Tools\Folder::scanForFile($pathToExtensionConfiguration, 'json');

        foreach ($extensionJsonList ?? [] as $json) {
            $jsonData = Tools\Json::read($pathToExtensionConfiguration . DIRECTORY_SEPARATOR . $json);
            if (($jsonData ?? false)) {
				Tools\ConfigArray::arrayMerge($return, $jsonData);
            }
        }

        return $return;
    }

    /**
     * @since 0.12
     */
    public function readVendorsAndExtensions(): void
    {
        $vendorsAndExtensions = [];

        $vendors = Tools\Folder::scanForDirectory($this->repositoryPath);

        foreach ($vendors ?? [] as $vendorKey => $vendorValue) {
            $jsonData = Tools\Json::read($this->repositoryPath . $vendorValue . DIRECTORY_SEPARATOR . 'vendor.json');

            if (json_last_error() == JSON_ERROR_NONE) {
                $vendorsAndExtensions[$vendorValue] = [];
                $vendorsAndExtensions[$vendorValue] = $jsonData['vendor'];
                $vendorsAndExtensions[$vendorValue]['extensions'] = [];

                $extensionList = Tools\Folder::scanForDirectory(
                    $this->repositoryPath
                    . $vendorValue . DIRECTORY_SEPARATOR
                    . 'TYPO3'
                );

                foreach ($extensionList ?? [] as $extensionKey => $extensionValue) {
                    $jsonData = Tools\Json::read(
                        $this->repositoryPath
                        . $vendorValue . DIRECTORY_SEPARATOR
                        . 'TYPO3'  . DIRECTORY_SEPARATOR
                        . $extensionValue . DIRECTORY_SEPARATOR
                        . 'extension.json'
                    );

                    if (json_last_error() == JSON_ERROR_NONE) {
                        $vendorsAndExtensions[$vendorValue]['extensions'][$extensionValue] = $jsonData;
                    } else {
// ToDo error handling
                    }
                }
            }
        }

        $this->vendorsAndExtensions = $vendorsAndExtensions;
    }


    // Area for build extensions

    /**
     * @since 0.12
     */
    public function build(
        string $beUserId,
        string $vendorName,
        string $extensionName,
    ): void {
		$buildOk = false;

        $this->beUserId = $beUserId;
        $this->vendorName = $vendorName;
        $this->extensionName = $extensionName;

        self::readDeveloper();

        if (!($this->developer ?? false)) {
            $this->buildResult = 'Error: Developer not fund!';
            return;
        }

        if (!($this->vendors[$vendorName] ?? false)) {
            $this->buildResult = 'Error: Vendor not fund!';
            return;
        }

        self::readExtension($this->vendorName, $this->extensionName);

        if (!($this->extension)) {
            $this->buildResult = 'Error: Extension not fund!';
            return;
        }

		if ($this->builderLocal && ($this->configuration['typo3']['buildLocal'] ?? false)) {
            $this->logger->info('Build - Local');

            $result = self::buildLocal();
        } else {
            $this->logger->info('Build - Remote');

            $result = self::buildRemote();
        }
		
		$this->buildResult = $result['status'] ?? '';

        switch ($result['status'] ?? 'error') {
            case '200 OK':
                $this->buildResult = 'OK';

                $debugPath =
                    $this->buildPath
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . 'TYPO3' . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'debug' . DIRECTORY_SEPARATOR;

                file_put_contents(
                    $debugPath . 'response.json',
                    json_encode($result, JSON_PRETTY_PRINT)
                );

                $importPath =
                    $this->buildPath
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . 'TYPO3' . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'import' . DIRECTORY_SEPARATOR;
                $buildPath =
                    $this->buildPath
                    . $this->vendorName . DIRECTORY_SEPARATOR
                    . 'TYPO3' . DIRECTORY_SEPARATOR
                    . $this->extensionName . DIRECTORY_SEPARATOR
                    . 'build'.DIRECTORY_SEPARATOR;

                $extension = $result['extension'];

                $zipName = basename((string)($result['zipName'] ?? $extension . '.zip'));

                // ToDo Error handling!
                if (!preg_match('/^[a-zA-Z0-9._-]+\.zip$/', $zipName)) {
                    throw new \RuntimeException('Invalid ZIP filename.', 1717500101);
                }

                $base64Zip = $result['base64Zip'];
                $base64Zip = str_replace('data:application/zip;base64,', '', $base64Zip);
                $base64Zip = str_replace(' ', '+', $base64Zip);

                // ToDo Error handling!
                $zipBinary = base64_decode($base64Zip, true);
                if ($zipBinary === false) {
                    throw new \RuntimeException('Invalid ZIP payload.', 1717500102);
                }

                file_put_contents($importPath . $zipName, $zipBinary, LOCK_EX);

                GeneralUtility::mkdir_deep($buildPath);
                Tools\ZipArchive::unzip($importPath.$zipName, $buildPath);

                if ($this->isComposerMode) {
                    $extPath =
                        $this->extensionPath
                        . $this->extension['extension']['extensionComposerName'];
	    		} else {
                    $extPath =
                        $this->extensionPath
                        . $this->extension['extension']['extensionName'] . DIRECTORY_SEPARATOR;
                }

                // Copy build ...
                self::assertPathIsInside($this->extensionPath, $extPath);

                GeneralUtility::rmdir($extPath, true);
                GeneralUtility::mkdir_deep($extPath);
                Tools\Folder::copy($buildPath, $extPath);

                if (PHP_SAPI === 'cli') {
                    self::chownr($this->t3tmpPath, $this->ownerInfo['uid'] );
                    self::chownr($this->t3tmpCorePath, $this->ownerInfo['uid'] );
                    self::chownr($extPath, $this->ownerInfo['uid'] );
                }
//ToDo was passiert mit $buildInfo
                if ($result['buildLocal'] ?? false) {
                    $buildInfo = 'Extension is build (local).'; // ToDo LLL
                } else {
                    $buildInfo = 'Extension is build (remote).'; // TodO LLL
                }

                break;
		}
	}

    /**
     * @since 0.12
     */
    private function buildLocal(): array
    {
        $multipart = self::buildRequest();

        $parsedBody = [];
        foreach ($multipart['multipart'] ?? [] as $multipartKey => $multipartValue) {
            $parsedBody[$multipartValue['name']] = $multipartValue['contents'];
        }

        $result = [];
        $result = json_decode(
            \ExtensionBuilder\ExtensionBuilderTypo3Core\BuildExtension::buildExtension($parsedBody),
            true
        );
        $result = ['buildLocal' => true] + $result;

        return $result;
	}

    /**
     * @since 0.12
     */
    private function buildRemote(): array
    {
        $multipart = self::buildRequest();

        $this->logger->info('Build - Remote',[
            'builderUrl' => $this->configuration['typo3']['builderUrl'],
            'builderApi' => $this->configuration['typo3']['builderApi'],
        ]);

        $buildRemoteStart = microtime(true);
        $result = Tools\RestApiClient::build(
            $this->configuration['typo3']['builderUrl'],
            $this->configuration['typo3']['builderApi'],
            $multipart,
        );

        $buildRemoteDuration = microtime(true)-$buildRemoteStart;

        $result['buildRemote'] = true;
        $result['buildRemoteDuration'] = $buildRemoteDuration;

        return $result;
	}

    /**
     * @since 0.12
     */
    private function buildRequest(): array
    {
        $extensionDevelopmentSourcePath =
            $this->repositoryPath
            . $this->vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR;

        $exportPath =
            $this->buildPath
            . $this->vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $this->extensionName . DIRECTORY_SEPARATOR;

        // Clean up
        GeneralUtility::rmdir($exportPath, true);

        GeneralUtility::mkdir_deep($exportPath . 'source');
        GeneralUtility::mkdir_deep($exportPath . 'debug');
        GeneralUtility::mkdir_deep($exportPath . 'import');
        GeneralUtility::mkdir_deep($exportPath . 'import');

        $extensionSourceExportPath = $exportPath . 'source' . DIRECTORY_SEPARATOR;

		Tools\Folder::copy($extensionDevelopmentSourcePath, $extensionSourceExportPath);

        // Copyback ToDo
        $extConf = self::readExtensionHelper($extensionDevelopmentSourcePath);
        if ($extConf['extensionBuild']['copyBack'] ?? false) {
            $extensionName = $extConf['extension']['extensionName'];
            $extPath =
                Environment::getPublicPath() . DIRECTORY_SEPARATOR
                . 'typo3conf' . DIRECTORY_SEPARATOR
                . 'ext' . DIRECTORY_SEPARATOR
                . $this->extensionName . DIRECTORY_SEPARATOR;
            $developerCodePath = $extensionDevelopmentSourcePath . 'DeveloperCode' . DIRECTORY_SEPARATOR;

            foreach ($extConf['extensionBuild']['copyBack'] ?? [] as $copyBackName => $copyBackData) {
                if ($copyBackData) {
                    $copyBackSorce = $extPath . $copyBackName;
                    $copyBackDestination = $developerCodePath . $copyBackName;
                    if (is_dir($copyBackSorce)) {
                        Tools\Folder::copy($copyBackSorce, $copyBackDestination);
    				} elseif (is_file($copyBackSorce)) {
                        $path_parts = pathinfo($copyBackDestination);
                        GeneralUtility::mkdir_deep($path_parts['dirname']);
                        copy($copyBackSorce, $copyBackDestination);
    				}
				}
		    }
        }

		$extensionSourcePath = $exportPath . 'source' . DIRECTORY_SEPARATOR;
		$extensionDebugPath = $exportPath . 'debug' . DIRECTORY_SEPARATOR;

        $multipart = [];
		$multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'build'];
        $multipart['multipart'][] = ['name' => 'version', 'contents' => $this->builderVersion];
        $multipart['multipart'][] = ['name' => 'jsonVersion', 'contents' => '1'];

        $multipart['multipart'][] = ['name' => 'developerId', 'contents' => $this->configuration['systemId']];

        $multipart['multipart'][] = ['name' => 'systemId', 'contents' => $this->developer['developerId'] ?? 'cli build']; // ToDo

        $multipart['multipart'][] = ['name' => 'serverIp', 'contents' => $_SERVER['SERVER_ADDR'] ?? '']; // ToDo CLI/Command no IP
        $multipart['multipart'][] = ['name' => 'serverMac', 'contents' => 'community']; // ToDo
        $multipart['multipart'][] = ['name' => 'vendorHash', 'contents' => '']; // ToDo Check for useing

        $multipart['multipart'][] = ['name' => 'vendor', 'contents' => $this->vendorName];
        $multipart['multipart'][] = ['name' => 'extension',  'contents' => $this->extensionName];

// ToDo
//        $dependentExtensionsList['dependenciesExport'] = $this->foreignExtensionsList; // Todo Docu / fuction check
//        Tools\Json::write(
//            $extensionSourcePath . 'extension.dependencies.export.json',
//            $dependentExtensionsList
//        );
	
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

    /**
     * @since 0.12
     */
    private function assertPathIsInside(string $basePath, string $targetPath): void
    {
        $baseReal = realpath($basePath);
        $targetParentReal = realpath(dirname($targetPath));

        if ($baseReal === false || $targetParentReal === false) {
            throw new \RuntimeException('Invalid path');
        }

        $baseReal = rtrim($baseReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $targetParentReal = rtrim($targetParentReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($targetParentReal, $baseReal)) {
            throw new \RuntimeException('Target path is outside allowed base path');
        }
    }

    // Helper ToDo move to tools

    /**
     * @since 0.12
     */
    private function chownr(
        string $path,
        int $owner
    ) {
        if (!is_dir($path)) {
            return chown($path, $owner);
		}
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path . '/' . $file;
                if (is_link($fullpath)) {
                    return FALSE;
                } elseif (!is_dir($fullpath) && !chown($fullpath, $owner)) {
                        return FALSE;
                } elseif (!self::chownr($fullpath, $owner)) {
                    return FALSE;
				}
            }
        }
        closedir($dh);
        if (chown($path, $owner)) {
            return TRUE;
		} else {
            return FALSE;
        }
    }

}