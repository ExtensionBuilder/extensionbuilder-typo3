<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Service;

use Doctrine\DBAL\ParameterType;
use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;

use TYPO3\CMS\Core\Package\PackageManager;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
class BackendService extends BuildService
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
        public string $beUserGroups = '',
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

        public array $projects = [],
        public bool $noProjects = true,
        public array $systemExtensions = [],
        public array $tcaTypesSelects = [],
        public array $configurationConfiguration = [],
        public array $developerConfiguration = [],
        public array $vendorConfiguration = [],
        public array $projectConfiguration = [],
        public array $extensionConfiguration = [],
        public array $devFolderStructure = [],
        public array $localExtensions = [],
        public array $foreignExtensions = [],
        public array $extensionFiles = [],
        public string $typo3Command = '',
        public string $lll = 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang',
    ) {
        parent::__construct(
            $packageManager,
            $logger,
            $ownerInfo,
            $isComposerMode,
            $coreStatus,
            $beUserId,
            $beUserIsAdmin,
            $beUserGroups,
            $projectPath,
            $repositoryPath,
            $extensionPath,
            $configurationData,
            $t3tmpPath,
            $t3tmpCorePath,
            $buildPath,
            $builderLocal,
            $builderLocalVersion,
            $builderVersion,
            $vendorName,
            $extensionName,
            $configuration,
            $developers,
            $developer,
            $noDeveloper,
            $vendors,
            $noVendors,
            $extension,
            $vendorsAndExtensions,
            $buildResult,
        );

        // .htaccess

        //    # Für EB Studio Beta
        //    RewriteRule ^studio/(.*)$ /typo3conf/ext/extensionbuilder_studio/Resources/Public/Html/ExtensionsManager/$1 [L,E=STUDIO:123]

        // RewriteEngine On

        // Find and save the location of the TYPO3 console command.
        $this->typo3Command = ExtensionManagementUtility::extPath('core');
        if ($this->isComposerMode) {
            $this->typo3Command = substr($this->typo3Command, 0, strpos($this->typo3Command, 'typo3/cms-core'));
        }
        $this->typo3Command .= 'bin/typo3';

        $extensionConfigurationPath
            = $this->packageManager->getPackage('extensionbuilder_typo3')->getPackagePath()
            . 'Configuration' . DIRECTORY_SEPARATOR
            . 'ExtensionBuilder' . DIRECTORY_SEPARATOR;

        $this->systemExtensions = require $extensionConfigurationPath . 'SystemExtensions.php';
        $this->tcaTypesSelects = require $extensionConfigurationPath . 'TcaTypesSelects.php';

        $this->configurationConfiguration = require $extensionConfigurationPath . 'ControllerConfiguration.php';
        $this->developerConfiguration = require $extensionConfigurationPath . 'ControllerDeveloper.php';
        $this->vendorConfiguration = require $extensionConfigurationPath . 'ControllerVendor.php';
        $this->projectConfiguration = require $extensionConfigurationPath . 'ControllerProject.php';
        $this->extensionConfiguration = require $extensionConfigurationPath . 'ControllerExtension.php';
        $this->extensionConfiguration['components'] = require $extensionConfigurationPath . 'ControllerComponents.php';

        $this->devFolderStructure = require $extensionConfigurationPath . 'ExtensionFolderStructure.php';

        // Check if a local installation of Extension Builder for TYPO3 Core is installed.
        if ($this->builderLocal = $this->packageManager->isPackageActive('extensionbuilder_typo3_core')) {
            $this->builderLocalVersion
                = $this->packageManager
                ->getPackage('extensionbuilder_typo3_core')
                ->getPackageMetaData()
                ->getVersion();
        }

        // Determine and note the Extension Builder TYPO3 editor version
        $this->builderVersion
            = $this->packageManager
            ->getPackage('extensionbuilder_typo3')
            ->getPackageMetaData()
            ->getVersion();

        self::readConfiguration();
        self::composerSetup();

        self::getForeignExtension();

        self::readVendors();
        self::readProjects();
    }

    /**
     * @since 0.12
     */
    final public function readConfiguration(): void
    {
        parent::readConfiguration();

        $updateConfiguration = true;

        if (!($this->configuration['systemId'] ?? false)) {
            $updateConfiguration = true;
            $this->configuration['systemId'] = Tools\Uuid::uuid();
        }

        if (!($this->configuration['systemSig'] ?? false)) {
            $updateConfiguration = true;
            $this->configuration['systemSig'] = Tools\Uuid::createSystemId($this->configuration['systemId']);
        }
        // ToDo Sig check

        if (!($this->configuration['typo3'] ?? false)) {
            $updateConfiguration = true;
            $this->configuration['typo3'] = [];
        }

        // Load fields from the configuration file and generate fields with default values ​​if they do not exist.
        foreach ($this->configurationConfiguration['fieldsEdit'] ?? [] as $fieldKey => $fieldValue) {
            if ($fieldValue['default'] ?? false) {

                $path = [];
                foreach ($fieldValue['array'] ?? [] as $pathKey => $pathValue) {
                    $path[] = $pathValue;
                }
                $path[] = $fieldKey;

                self::setNestedValue($this->configuration, $path, $fieldValue['default']);
            }
        }

        if (!($BackendGroupBy = self::checkBackendGroupByTitle('EB TYPO3 - Developer'))) {
            $BackendGroupBy = $this->configuration['typo3']['backendGroupId'] ?? 0;
            $BackendGroupBy = self::createBackendGroup(
                'EB TYPO3 - Developer',
                'Extension Builder Developer',
                'extensionbuilder, extensionbuilder_typo3',
                $BackendGroupBy,
            );
        }

        if (!($this->configuration['typo3']['backendGroupId'] ?? false)) {
            $updateConfiguration = true;
            $this->configuration['typo3']['backendGroupId'] = $BackendGroupBy;
        }

        if ($updateConfiguration) {
            self::writeConfiguration();
        }

        if (!is_dir($this->repositoryPath)) {
            GeneralUtility::mkdir_deep($this->repositoryPath);
        }

        if ($this->isComposerMode) {
            // ToDo Check
            $packagesPath = ''
                . Environment::getProjectPath() . DIRECTORY_SEPARATOR
                . $this->configuration['typo3']['developerRepository'];
            $composerJson = ''
                . Environment::getProjectPath() . DIRECTORY_SEPARATOR
                . 'composer.json';

            if (!is_dir($packagesPath)) {
                GeneralUtility::mkdir_deep($packagesPath);
            }

            $composer = Tools\Json::read($composerJson);
            $composerPath = $this->configuration['typo3']['composerRepository'] . '/*';

            if ($composer['repositories'] ?? false) {
                $addRepositories = true;
                $count = count($composer['repositories']);
                foreach ($composer['repositories'] ?? [] as $repositorie) {
                    if ($repositorie['url'] === $composerPath) {
                        $addRepositories = false;
                        break;
                    }
                }
            } else {
                $count = 0;
                $addRepositories = true;
            }

            if ($addRepositories) {
                $composer['repositories'][$count] = [];
                $composer['repositories'][$count]['type'] = 'path';
                $composer['repositories'][$count]['url'] = $composerPath;
                $composer['repositories'][$count]['options'] = [];
                $composer['repositories'][$count]['options']['symlink'] = true;

                Tools\Json::write($composerJson, $composer);
            }
        } else {
            $htaccessFile = $this->repositoryPath . '.htaccess';
            if (!is_file($htaccessFile)) {
                file_put_contents(
                    $htaccessFile,
                    "<IfModule mod_authz_core.c>\n"
                    . "    Require all denied\n"
                    . "</IfModule>\n"
                );
            }
        }
    }

    /**
     * @since 0.12
     */
    private function setNestedValue(array &$array, array $path, mixed $value): void
    {
        $current = &$array;

        foreach ($path as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current = $value;
    }

    /**
     * Check if the prerequisites for Composer are met and add any necessary ones.
     *
     * @since 0.12
     */
    final public function composerSetup(): void
    {
        if ($this->isComposerMode) {
            $addUrl = false;

            $composer = Tools\Json::read($this->projectPath . 'composer.json');

            $repository = $this->configuration['typo3']['composerRepository'];

            if (!(is_dir($this->projectPath . $repository))) {
                GeneralUtility::mkdir_deep($this->projectPath . $repository);
            }

            if ($composer['repositories'] ?? false) {
                $addUrl = true;
                foreach ($composer['repositories'] ?? [] as $composerValue) {
                    if (($composerValue['type'] ?? '') === 'path'
                        && (($composerValue['url'] ?? '') === $repository . '/*')
                    ) {
                        $addUrl = false;
                        break;
                    }
                }
            } else {
                $addUrl = true;
                $composer['repositories'] = [];
            }

            if ($addUrl) {
                $composer['repositories'][] = [
                    'type' => 'path',
                    'url' => $repository . '/*',
                    'options' => [
                        'symlink' => true,
                    ],
                ];

                Tools\Json::write($this->projectPath . DIRECTORY_SEPARATOR . 'composer.json', $composer);
            }
        }
    }

    /**
     * @since 0.12
     */
    final public function getLocalExtensions(array $extensionExtensions = []): void
    {
        $localExtensions = [];

        foreach ($this->systemExtensions ?? [] as $extensionKey => $extensionValue) {

            if (
                $extensionValue['selectable']
                && !($extensionExtensions[$extensionKey] ?? false)
            ) {
                $localExtensions[$extensionKey] = $extensionKey;
            }
        }

        //        $extensionsPath =
        //            Environment::getProjectPath() . DIRECTORY_SEPARATOR
        //            . 'typo3conf' . DIRECTORY_SEPARATOR
        //            . 'ext' . DIRECTORY_SEPARATOR;

        //        foreach ($this->localExtensions ?? [] as $extensionName => $extensionData) {
        //            $tmpFile = $extensionsPath . DIRECTORY_SEPARATOR . $extensionName . DIRECTORY_SEPARATOR . 'eb_ext_export.json';
        //            if (file_exists($tmpFile)) {
        //                $jsonData = Tools\Json::read($tmpFile);
        //                if ($jsonData ?? false) {
        //                    $jsonData = $jsonData ?? []; // Remove node
        //				    Tools\ConfigArray::arrayMerge($returnArray, $jsonData);
        //                }
        //	        }
        //		}

        $this->localExtensions = $localExtensions;
    }

    /**
     * @since 0.12
     */
    final public function getForeignExtension(): void
    {

        // ToDo Besser beim laden der Extsenun und mit überprüfung der abjhägikeit

        $returnArray = [];

        // Searches the extensions for eb_ext export.json and reads it and returns an array.
        // ToDo Description
        // ToDo only load dependencies ext

        $extensionsPath = ''
            . Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3conf' . DIRECTORY_SEPARATOR
            . 'ext' . DIRECTORY_SEPARATOR;

        foreach ($this->localExtensions ?? [] as $extensionKey => $extensionValue) {
            $file = ''
                . $extensionsPath . DIRECTORY_SEPARATOR
                . $extensionKey . DIRECTORY_SEPARATOR
                . 'Configuration' . DIRECTORY_SEPARATOR
                . 'ExtensionExport.json';
            if (file_exists($file)) {
                $jsonData = Tools\Json::read($file);
                if ($jsonData ?? false) {
                    $jsonData = $jsonData ?? []; // Remove node
                    Tools\ConfigArray::arrayMerge($returnArray, $jsonData);
                }
            }
        }

        $this->foreignExtensions = $returnArray;
    }

    /**
     * @since 0.12
     */
    final public function writeConfiguration(): void
    {
        $configurationJson = [];
        $configurationJson['configuration'] = $this->configuration;

        Tools\ConfigArray::changeToBool($configurationJson); // ToDo Check

        Tools\Json::write($this->configurationData, $configurationJson);
    }

    /**
     * @since 0.12
     */
    final public function writeDeveloper(): void
    {
        $developer = [];
        $developer['developer'] = $this->developer;

        $this->noDeveloper = false;

        Tools\ConfigArray::changeToBool($developer);
        Tools\Json::write(
            $this->repositoryPath . 'developer.' . $GLOBALS['BE_USER']->user['username'] . '.json',
            $developer
        );
    }

    /**
     * @since 0.12
     */
    final public function countDeveloper(): int
    {
        return count(
            Tools\Folder::scanForFile(
                $this->repositoryPath,
                filter: 'developer.'
            ) ?? []
        );
    }

    /**
     * @since 0.12
     */
    public function readVendors(): void
    {
        parent::readVendors();

        foreach ($this->vendors ?? [] as $vendorKey => $vendorValue) {
            if (!self::checkBackendGroupByTitle('EB TYPO3 - Vendor - ' . $vendorKey)) {
                $this->writeVendor($vendorKey);
            }
        }
    }

    /**
     * @since 0.12
     */
    final public function writeVendor(
        string $vendorName,
    ): void {
        $this->noVendors = false;

        $vendorData = $this->vendors[$vendorName];

        if (!self::checkBackendGroupByTitle('EB TYPO3 - Vendor - ' . $vendorName)) {
            $backendGroupId = (int)($vendorData['backendGroupId'] ?? '');
            $vendorData['backendGroupId'] = self::createBackendGroup(
                'EB TYPO3 - Vendor - ' . $vendorName,
                $vendorData['description'] ?? '',
                backendGroupId: $backendGroupId,
            );
        }

        $filePath = $this->repositoryPath . $vendorName . DIRECTORY_SEPARATOR;
        if (!is_dir($filePath)) {
            GeneralUtility::mkdir_deep($filePath);
        }

        // Trim please
        // ToDo recusive move to tools
        foreach ($vendorData as $key => $vendorField) {
            if (is_string($vendorField)) {
                $vendorData[$key] = trim($vendorField);
            }
        }

        $vendor = [];
        $vendor['vendor'] = $vendorData;

        Tools\ConfigArray::changeToBool($vendor);
        Tools\Json::write($filePath . 'vendor.json', $vendor);
    }

    /**
     * Deletes the vendor and all their projects
     *
     * @since 0.12
     */
    final public function deleteVendor(
        string $vendorName,
    ): void {
        self::deleteBackendGroupByTitle('EB TYPO3 - Vendor - ' . $vendorName);

        unset($this->vendors[$vendorName]);

        GeneralUtility::rmdir($this->repositoryPath . $vendorName, true);
    }

    /**
     * @since 0.12
     */
    final public function getVendors(): array
    {
        $array = [];
        $array['no'] = 'Please select';

        foreach ($this->vendors ?? [] as $vendorKey => $vendorValue) {
            if (
                ($this->beUserIsAdmin)
                || ($this->userHasBackendGroup((int)($vendorValue['backendGroupId'] ?? 0)))
            ) {
                $array[$vendorValue['vendorName']] = $vendorValue['vendorName'];
            }
        }

        return $array;
    }

    /**
     * @since 0.12
     */
    final public function readProjects(): void
    {
        $this->projects = [];

        $fileNameGlobalProjects = $this->repositoryPath . 'globalProjects.json';

        // Load global projects
        if (file_exists($fileNameGlobalProjects)) {
            $projects = Tools\Json::read($fileNameGlobalProjects);
            $this->projects = array_merge($this->projects, ($projects['projects'] ?? []));
        }

        // Load vendor projects
        foreach ($this->vendors ?? [] as $vendorKey => $vendorValue) {
            foreach ($vendorValue['projects'] ?? [] as $projectKey => $projectValue) {
                $this->projects[$projectKey] = $projectValue;
            }
        }

        // Load vendor developer
        $this->projects = array_merge($this->projects, ($this->developer['projects'] ?? []));

        foreach ($this->projects ?? [] as $projectKey => $projectValue) {
            $this->noProjects = false;
            $extensions = [];
            $dependencies = [];

            foreach (($projectValue['extensions'] ?? []) as $extensionKey => $extensionValue) {
                foreach ($this->vendorsAndExtensions ?? [] as $vendorsKey => $vendorsValue) {
                    foreach ($vendorsValue['extensions'] ?? [] as $vendorExtensionKey => $vendorExtensionValue) {
                        if ($vendorExtensionKey === $extensionKey) {
                            $extensions[$vendorExtensionKey] = $vendorExtensionValue;
                            $extensions[$vendorExtensionKey]['extensionOnOff'] = $extensionValue;
                            if ($vendorExtensionValue['extension']['depends'] ?? false) {
                                foreach (($vendorExtensionValue['extension']['depends'] ?? []) as $dependKey => $dependValue) {
                                    foreach ($this->vendorsAndExtensions ?? [] as $vendorsKey => $vendorsValue) {
                                        foreach ($vendorsValue['extensions'] ?? [] as $vendorExtensionKey => $vendorExtensionValue) {
                                            if ($vendorExtensionKey === $dependKey) {
                                                $dependencies[$vendorExtensionKey] = $vendorExtensionValue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->projects[$projectKey]['extensions'] = $extensions;
            $this->projects[$projectKey]['dependencies'] = $dependencies;
        }
    }

    /**
     * @since 0.12
     */
    final public function writeProjects(): void
    {
        $fileNameGlobalProjects = $this->repositoryPath . 'globalProjects.json';

        $projects = $this->projects;

        $globalProjects = [];
        $vendorProjects = [];
        $developerProjects = [];

        foreach ($projects ?? [] as $projectKey => $projectValue) {
            switch ((string)($projectValue['scope'] ?? 'no')) {
                case 'no':
                    break;
                case 'global':

                    $globalProjects[$projectKey] = $projectValue;

                    //echo 'X ';
                    //                    $globalProjects[$projectKey] = $projectValue;

                    //        if ($globalProjects) {
                    //			$globalProjectsX = [];
                    //            $globalProjectsX['projects'] = $globalProjects;
                    //            Tools\Json::write($fileNameGlobalProjects, $globalProjectsX);
                    //        } else {
                    //			if (file_exists($fileNameGlobalProjects)) {
                    //                unlink($fileNameGlobalProjects);
                    //			}
                    //		}

                    break;
                case 'vendor':
                    //                    if (!array_key_exists('vendorName', $projectValue)) {
                    //                        $vendorProjects[$projectValue['vendorName']] = [];
                    //                    }
                    //                    $vendorProjects[$projectValue['vendorName']][$projectKey] = $projectValue;
                    break;
                case 'developer':

                    //                    $developerProjects[$projectKey] = $projectValue;
                    break;
            }

            foreach ($projectValue['extensions'] ?? [] as $extensionKey => $extensionValue) {
                $extensionOnOff = $projects[$projectKey]['extensions'][$extensionKey]['extensionOnOff'];
                unset($projects[$projectKey]['extensions'][$extensionKey]);
                $projects[$projectKey]['extensions'][$extensionKey] = $extensionOnOff;
            }
            if (!($projects[$projectKey]['extensions'] ?? false)) {
                $projects[$projectKey]['extensions'] = [];
            }
            unset($projects[$projectKey]['dependencies']);

        }

        if ($globalProjects) {

            $globalProjectsX = [];
            $globalProjectsX['projects'] = $globalProjects;
            Tools\Json::write($fileNameGlobalProjects, $globalProjectsX);
        } else {
            if (file_exists($fileNameGlobalProjects)) {
                unlink($fileNameGlobalProjects);
            }
        }

        // ToDo
        return;

        foreach ($vendorProjects ?? [] as $vendorKey => $vendorValue) {
            $this->vendors[$vendorKey]['projects'] = [];
            foreach ($vendorValue ?? [] as $projectKey => $projectsValue) {
                $this->vendors[$vendorKey]['projects'][$projectKey] = $projectsValue;
            }

            self::writeVendor($vendorKey);
        }

        if ($developerProjects) {
            $this->developer['projects'] = $developerProjects;
        } else {
            $this->developer['projects'] = [];
        }

        //        self::writeDeveloper();
    }

    /**
     * @since 0.12
     */
    public function readExtension(
        string $vendorName,
        string $extensionName,
    ): void {
        parent::readExtension($vendorName, $extensionName);

        $this->extension['selections'] = [];

        $developerCodePath
            = $this->repositoryPath
            . $vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'DeveloperCode' . DIRECTORY_SEPARATOR;

        $this->extension['selections']['Icons'] = self::readIcons($developerCodePath) ?? [];
        $this->extension['selections']['Images'] = self::readImages($developerCodePath) ?? [];
        $this->extension['selections']['Javascripts'] = self::readJavascripts($developerCodePath) ?? [];
    }

    /**
     * @since 0.14
     */
    private function readIcons(
        string $developerCodePath,
    ): array {
        $iconsPath = ''
            . $developerCodePath
            . 'Resources' . DIRECTORY_SEPARATOR
            . 'Public' . DIRECTORY_SEPARATOR
            . 'Icons' . DIRECTORY_SEPARATOR;

        return Tools\Folder::scanForFile($iconsPath);
    }

    /**
     * @since 0.14
     */
    private function readImages(
        string $developerCodePath,
    ): array {
        $imagesPath = ''
            . $developerCodePath
            . 'Resources' . DIRECTORY_SEPARATOR
            . 'Public' . DIRECTORY_SEPARATOR
            . 'Images' . DIRECTORY_SEPARATOR;

        return Tools\Folder::scanForFile($imagesPath);
    }

    /**
     * @since 0.14
     */
    private function readJavascripts(
        string $developerCodePath,
    ): array {
        $javaScriptPath = ''
            . $developerCodePath
            . 'Resources' . DIRECTORY_SEPARATOR
            . 'Public' . DIRECTORY_SEPARATOR
            . 'JavaScript' . DIRECTORY_SEPARATOR;

        return Tools\Folder::scanForFile($javaScriptPath);
    }

    /**
     * @since 0.12
     */
    public function writeExtension(
        string $vendorName,
        string $extensionName,
    ): void {
        if (!($this->extension['extensionBuild'] ?? false)) {
            $this->extension['extensionBuild'] = [];
        }

        $this->extension['extensionBuild']['jsonVersion'] = 1;
        $this->extension['extensionBuild']['editorVersion'] = $this->builderVersion;

        $vendorPath = $this->repositoryPath . $vendorName . DIRECTORY_SEPARATOR . 'TYPO3';
        GeneralUtility::mkdir_deep($vendorPath);

        $extensionPath = $vendorPath . DIRECTORY_SEPARATOR . $extensionName;
        GeneralUtility::mkdir_deep($extensionPath);

        $developerCodePath = $extensionPath . DIRECTORY_SEPARATOR . 'DeveloperCode' . DIRECTORY_SEPARATOR;
        foreach ($this->devFolderStructure ?? [] as $devFolderKey => $devFolderValue) {
            $dir = $developerCodePath . $devFolderValue;
            if (!is_dir($dir)) {
                GeneralUtility::mkdir_deep($dir);
            }
        }

        $this->extension['extension']['extensionNameLegacy'] = $this->extension['extension']['extensionName'];

        foreach ($this->extension ?? [] as $extensionKey => $extensionValue) {
            switch ($extensionKey) {
                case 'components':
                    foreach ($this->extensionConfiguration['components'] ?? [] as $componentsKey => $componentsValue) {
                        $extensionValueForJson = [];
                        if ($extensionValue[$componentsKey] ?? false) {
                            switch ($componentsKey) {
                                case 'models':
                                case 'enums':
                                    foreach ($extensionValue[$componentsKey] ?? [] as $componentKey => $componentValue) {
                                        $extensionValueForJson['components'][$componentsKey] = [];
                                        $extensionValueForJson['components'][$componentsKey][$componentKey] = [];
                                        $extensionValueForJson['components'][$componentsKey][$componentKey] =  $componentValue;

                                        self::writeExtensionHelper(
                                            $extensionPath,
                                            substr(
                                                $componentsKey,
                                                0,
                                                strlen($componentsKey) - 1
                                            ) . '.' . $componentKey . '.json',
                                            $extensionValueForJson
                                        );
                                    }
                                    break;
                                default:
                                    $extensionValueForJson['components'][$componentsKey] = [];
                                    $extensionValueForJson['components'][$componentsKey]
                                        = $extensionValue[$componentsKey];

                                    self::writeExtensionHelper(
                                        $extensionPath,
                                        $componentsKey . '.json',
                                        $extensionValueForJson
                                    );
                            }
                        }
                    }
                    break;
                default:
                    $extensionValueForJson = [];
                    $extensionValueForJson[$extensionKey] = $extensionValue;

                    self::writeExtensionHelper(
                        $extensionPath,
                        $extensionKey . '.json',
                        $extensionValueForJson
                    );
            }
        }
    }

    /**
     * @since 0.12
     */
    private function writeExtensionHelper(
        string $pathToConfigurationJson,
        string $fileName,
        array $arrayForJson,
    ): void {
        GeneralUtility::mkdir_deep($pathToConfigurationJson);

        Tools\ConfigArray::changeToBool($arrayForJson);

        Tools\Json::write(
            $pathToConfigurationJson . DIRECTORY_SEPARATOR . $fileName,
            $arrayForJson,
        );
    }

    // ToDo

    /**
     * @since 0.12
     */
    private function readDeveloperCodeHelper(
        string $pathToExtensionDeveloperCode,
    ): array {
        $return = [];

        $publicPath = ''
            . 'DeveloperCode' . DIRECTORY_SEPARATOR
            . 'Resources' . DIRECTORY_SEPARATOR
            . 'Public' . DIRECTORY_SEPARATOR;

        foreach (['Css', 'Fonts', 'Icons', 'Images', 'JavaScript', 'Scss'] as $json) {
            $resourcesPublic = 'resourcesPublic' . $json;

            if (!($return[$resourcesPublic] ?? false)) {
                $return[$resourcesPublic] = [];
            }

            //            $filesX = Tools\Folder::scanForFile($pathToExtensionConfiguration . $publicPath . $json);

            //foreach ($filesX ?? [] as $jsonX) {
            //            if (!($return[$resourcesPublic][$jsonX] ?? false)) {
            //                $return[$resourcesPublic][$jsonX] = [];
            //            }
            //}

            // $$resourcesPublic =

            // Change Prüfen

        }

        // if updetw write Ext
        return $return;
    }

    /**
     * @since 0.12
     */
    public function deleteExtension(
        string $vendorName,
        string $extensionName,
    ): void {
        if ($this->vendorsAndExtensions[$vendorName] ?? false) {
            $vendorFolder = $this->repositoryPath . $vendorName;

            // ToDo -> if ($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] ?? false)
            if ($extensionName) {
                if ($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName] ?? []) {
                    $extensionsFolder
                        = $vendorFolder . DIRECTORY_SEPARATOR
                        . 'TYPO3' . DIRECTORY_SEPARATOR
                        . $extensionName;

                    unset($this->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]);

                    Tools\Folder::delete($extensionsFolder);
                }
            }
        }
    }

    /**
     * @since 0.12
     */
    public function checkName(
        ActionController $actionController,
        string &$name,
    ): bool {

        if (!preg_match('#^[a-zA-Z0-9]+$#', $name ?? '')) {
            $actionController->flashMessage(
                '',
                // ToDo LLL
                //LocalizationUtility::translate($this->lll .'.extension.xlf:specifyextensionname'),
                'Only letters and numbers allowed.',
            );
            return false;
        }
        if (preg_match('#^[0-9]+$#', $name ?? '')) {
            $actionController->flashMessage(
                '',
                // ToDo LLL
                //LocalizationUtility::translate($this->lll .'.extension.xlf:specifyextensionname'),
                'mindesetns ein bucstaben',
            );
            return false;
        }
        if (!($name ?? false)) {
            $actionController->flashMessage(
                '',
                // ToDo LLL
                //LocalizationUtility::translate($this->lll .'.extension.xlf:specifyextensionname'),
                'keine name',
            );
            return false;
        }

        return true;
    }

    /**
     * @since 0.12
     */
    public function writeExtensionComponents(
        string $vendorName,
        string $extensionName,
        string $componentsName,
    ): void {
        $extensionsFolder = ''
            . $this->repositoryPath
            . $vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR;

        $componentData = $this->extension['components'][$componentsName];

        switch ($componentsName) {
            case 'models':
                foreach ($componentData ?? [] as $componentDataKey => $componentDataData) {
                    $componentDataForJson = [];
                    $componentDataForJson['components'] = [];
                    $componentDataForJson['components'][$componentsName] = [];
                    $componentDataForJson['components'][$componentsName][$componentDataKey] = $componentData[$componentDataKey];

                    self::writeExtensionHelper(
                        $extensionsFolder,
                        'model.' . $componentDataKey . '.json',
                        $componentDataForJson,
                    );
                }
                break;

            case 'enums':
                foreach ($componentData ?? [] as $componentDataKey => $componentDataData) {
                    $componentDataForJson = [];
                    $componentDataForJson['components'] = [];
                    $componentDataForJson['components'][$componentsName] = [];
                    $componentDataForJson['components'][$componentsName][$componentDataKey] = $componentData[$componentDataKey];

                    self::writeExtensionHelper(
                        $extensionsFolder,
                        'enum.' . $componentDataKey . '.json',
                        $componentDataForJson,
                    );
                }
                break;

            default:
                $componentDataForJson = [];
                $componentDataForJson['components'] = [];
                $componentDataForJson['components'][$componentsName] = $componentData;

                self::writeExtensionHelper(
                    $extensionsFolder,
                    $componentsName . '.json',
                    $componentDataForJson,
                );
        }
    }

    /**
     * @since 0.12
     */
    public function writeExtensionComponent(
        ActionController $actionController,
        string $vendorName,
        string $extensionName,
        string $componentsName,
        string $componentName,
        array $componentDataNew,
    ): void {
        $componentData = &$this->extension['components'][$componentsName] ?? [];
        if (is_null($componentData)) {
            $componentData = [];
        }

        $componentDataForMerge = [];
        $componentDataForMerge[$componentName] = $componentDataNew;

        Tools\ConfigArray::arrayMerge($componentData, $componentDataForMerge);

        unset($this->extension['components'][$componentsName]);

        $this->extension['components'][$componentsName] = $componentData;

        self::writeExtensionComponents(
            $vendorName,
            $extensionName,
            $componentsName
        );

        $actionController->flashMessage(
            '',
            LocalizationUtility::translate(
                $this->lll . '.component.xlf:flashMessage.save',
                '',
                [$this->extensionConfiguration['components'][$componentsName]['title'], $componentName]
            ),
        );
    }

    /**
     * @since 0.12
     */
    public function deleteExtensionComponent(
        ActionController $actionController,
        string $vendorName,
        string $extensionName,
        string $componentsName,
        string $componentName,
    ): void {
        unset($this->extension['components'][$componentsName][$componentName]);

        if (count($this->extension['components'][$componentsName])) {
            self::writeExtensionComponents(
                $vendorName,
                $extensionName,
                $componentsName,
            );
        } else {
            // The component no longer contains any elements and can be deleted.
            switch ($componentsName) {
                case 'beModels':
                    $unlinkFile = 'beModels.' . $componentName;
                    break;
                case 'feModels':
                    $unlinkFile = 'feModels.' . $componentName;
                    break;
                case 'models':
                    $unlinkFile = 'model.' . $componentName;
                    break;
                case 'enums':
                    $unlinkFile = 'enum.'. $componentName;
                    break;
                default:
                    $unlinkFile = $componentsName;
            }

            $unlinkFile
                = $this->repositoryPath
                . $vendorName . DIRECTORY_SEPARATOR
                . 'TYPO3' . DIRECTORY_SEPARATOR
                . $extensionName . DIRECTORY_SEPARATOR
                . $unlinkFile . '.json';

            if (is_file($unlinkFile)) {
                unlink($unlinkFile);
            }
        }

        $actionController->flashMessage(
            '',
            LocalizationUtility::translate(
                $this->lll . '.component.xlf:flashMessage.delete',
                '',
                [$this->extensionConfiguration['components'][$componentsName]['title'], $componentName]
            ),
        );
    }

    /**
     * @since 0.12
     */
    public function writeExtensionProperty(
        ActionController $actionController,
        string $vendorName,
        string $extensionName,
        string $componentsName,
        string $componentName,
        string $propertysName,
        string $propertyName,
        array $propertyDataNew,
    ): void {
        $componentData = $this->extension['components'][$componentsName][$componentName] ?? [];

        $propertyDataForMerge = [];
        $propertyDataForMerge[$propertyName] = $propertyDataNew;

        if (!($componentData['propertys'] ?? false)) {
            $componentData['propertys'] = [];
        }

        if (!($componentData['propertys'][$propertysName] ?? false)) {
            $componentData['propertys'][$propertysName] = [];
        }

        Tools\ConfigArray::arrayMerge($componentData['propertys'][$propertysName], $propertyDataForMerge);

        $this->extension['components'][$componentsName][$componentName] = $componentData;

        self::writeExtensionComponents(
            $vendorName,
            $extensionName,
            $componentsName
        );

        $actionController->flashMessage(
            '',
            LocalizationUtility::translate(
                $this->lll . '.component.xlf:flashMessage.save',
                '',
                [$this->extensionConfiguration['components'][$componentsName]['propertys'][$propertysName]['title'], $propertyName]
            ),
        );
    }

    /**
     * @since 0.12
     */
    public function deleteExtensionProperty(
        ActionController $actionController,
        string $vendorName,
        string $extensionName,
        string $componentsName,
        string $componentName,
        string $propertysName,
        string $propertyName,
    ): void {
        unset(
            $this->extension
                ['components'][$componentsName][$componentName]
                ['propertys'][$propertysName][$propertyName]
        );

        if (
            count(
                $this->extension
                ['components'][$componentsName][$componentName]
                ['propertys'][$propertysName] ?? []
            ) == 0
        ) {
            unset(
                $this->extension
                ['components'][$componentsName][$componentName]
                ['propertys'][$propertysName]
            );
            if (
                count(
                    $this->extension
                    ['components'][$componentsName][$componentName]
                    ['propertys'] ?? []
                ) == 0
            ) {
                unset(
                    $this->extension
                    ['components'][$componentsName][$componentName]
                    ['propertys']
                );
            }
        }

        self::writeExtensionComponents(
            $vendorName,
            $extensionName,
            $componentsName
        );

        $actionController->flashMessage(
            '',
            LocalizationUtility::translate(
                $this->lll . '.component.xlf:flashMessage.delete',
                '',
                [
                    $this->extensionConfiguration['components'][$componentsName]['propertys'][$propertysName]['title'],
                    $propertyName,
                ],
            ),
        );
    }

    // Area for generating the extension

    /**
     * Calls the parent build function, outputs info, executes TYPO3 commands
     *
     * Outputs the result as Flash info.<br>
     * Executes configured TYPO3 commands (cache flush, dump autoload ...).
     *
     * @since 0.12
     */
    final public function build(
        string $beUserId,
        string $vendorName,
        string $extensionName,
    ): void {
        //9999
        //        self::sendNotification(
        //                    '',
        //                    'Info', //LocalizationUtility::translate($this->lll . '.extension.xlf:build.clearedcaches'),
        //                    ContextualFeedbackSeverity::OK,
        //        );

        $this->logger->info('Backend - Bulid');

        parent::build(
            $beUserId,
            $vendorName,
            $extensionName,
        );

        if ($this->buildResult == 'OK') {

            $this->logger->info('Backend - Build - OK');

            // ToDo sendNotification -> Local / Remoote / Dev

            if (!($this->packageManager->isPackageActive($this->extension['extension']['extensionName']))) {
                self::sendNotification(
                    '',
                    LocalizationUtility::translate($this->lll . '.extension.xlf:build.notactivated'),
                    ContextualFeedbackSeverity::WARNING,
                );
            }

            if ($this->developer['typo3']['cacheFlush'] ?? false) {
                self::processT3Command('cache:flush');

                self::sendNotification(
                    '',
                    LocalizationUtility::translate($this->lll . '.extension.xlf:build.cacheflush'),
                    ContextualFeedbackSeverity::OK,
                );
            }

            if ($this->developer['typo3']['cacheWarmup'] ?? false) {
                self::processT3Command('cache:warmup');

                self::sendNotification(
                    '',
                    LocalizationUtility::translate($this->lll . '.extension.xlf:build.cachewarmup'),
                    ContextualFeedbackSeverity::OK,
                );
            }

            if ($this->developer['typo3']['dumpAutoload'] ?? false) {
                if (!Environment::isComposerMode()) {

                    self::processT3Command('dumpautoload');

                    self::sendNotification(
                        '',
                        LocalizationUtility::translate($this->lll . '.extension.xlf:build.dumpedloading'),
                        ContextualFeedbackSeverity::OK,
                    );
                }
            }

            if ($this->developer['typo3']['databaseStructure'] ?? false) {

                self::processT3Command('database:updateschema');

                // database:updateschema safe
                // atabase:updateschema destructive

                self::sendNotification(
                    '',
                    LocalizationUtility::translate($this->lll . '.extension.xlf:build.clearedcaches'),
                    ContextualFeedbackSeverity::OK,
                );
            }
        } else {

            // 8888
            // ToDo Modal Error

            //            $this->logger->info('Backend - Build - Erro');

            self::sendNotification(
                $this->buildMessage,
                LocalizationUtility::translate($this->lll . '.extension.xlf:build.errorhasoccurred'),
                ContextualFeedbackSeverity::ERROR,
            );
        }
    }

    /**
     * @since 0.12
     */
    public function userHasBackendGroup(int $groupId): bool
    {
        if ($this->beUserIsAdmin) {
            return true;
        }

        if ($groupId <= 0) {
            return false;
        }

        $groupIds = GeneralUtility::intExplode(
            ',',
            (string)$this->beUserGroup,
            true
        );

        return in_array($groupId, $groupIds, true);
    }

    /**
     * @since 0.12
     */
    public function canAccessVendor(string $vendorName): bool
    {
        if ($this->beUserIsAdmin) {
            return true;
        }

        $vendor = $this->vendors[$vendorName] ?? null;

        if (!is_array($vendor)) {
            return false;
        }
        // ToDo zum testen der funktion
        //return false;
        return $this->userHasBackendGroup((int)($vendor['backendGroupId'] ?? 0));
    }

    /**
     * @since 0.12
     */
    public function assertCanAccessVendor(string $vendorName): void
    {
        if (!$this->canAccessVendor($vendorName)) {
            throw new \RuntimeException('Access denied for vendor');
        }
    }

    /**
     * @since 0.12
     */
    private function processT3Command(
        string $command,
    ): void {
        $process = new Process([
            '/usr/bin/php',
            '-d',
            'memory_limit=' . (ini_get('memory_limit') ?: '2G'),
            $this->typo3Command,
            $command,
        ]);

        $process->setWorkingDirectory(Environment::getProjectPath());
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @since 0.12
     */
    private function sendNotification(
        string $info1,
        string $info2,
        ContextualFeedbackSeverity $feedback,
    ): void {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $info1,
            $info2,
            $feedback,
        );
        $notificationQueue->enqueue($flashMessage);
    }

    // Backend group

    /**
     * @since 0.12
     */
    private function createBackendGroup(
        string $title,
        string $description = '',
        string $groupMods = '',
        int $backendGroupId = 0,
    ): int {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('be_groups');

        $queryBuilder = $connection->createQueryBuilder();

        if ($backendGroupId) {
            $queryBuilder
                ->getRestrictions()
                ->removeAll();

            $existingGroup = $queryBuilder
                ->select('uid', 'deleted')
                ->from('be_groups')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter((int)$backendGroupId, ParameterType::INTEGER)
                    )
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();

            if ($existingGroup) {
                $queryBuilder
                    ->update('be_groups')
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($backendGroupId))
                    )
                    ->set('deleted', 0)
                    ->set('hidden', 0)
                    ->set('tstamp', time())
                    ->executeStatement();
            } else {
                $queryBuilder
                    ->insert('be_groups')
                    ->values([
                        'uid' => (int)$backendGroupId,
                        'pid' => 0,
                        'title' => $title,
                        'description' => $description,
                        'groupMods' => $groupMods,
                        'crdate' => time(),
                        'tstamp' => time(),
                    ])
                ->executeStatement();
            }

        } else {
            $queryBuilder
                ->insert('be_groups')
                ->values([
                    'pid' => 0,
                    'title' => $title,
                    'description' => $description,
                    'groupMods' => $groupMods,
                    'crdate' => time(),
                    'tstamp' => time(),
                ])
                ->executeStatement();

            $backendGroupId = (int)$connection->lastInsertId();
        }

        return $backendGroupId;
    }

    /**
     * @since 0.12
     */
    private function deleteBackendGroupByTitle(
        string $title,
    ): void {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_groups');

        $queryBuilder
            ->delete('be_groups')
            ->where(
                $queryBuilder->expr()->eq(
                    'title',
                    $queryBuilder->createNamedParameter($title, ParameterType::STRING),
                )
            )
            ->executeStatement();
    }

    /** Check if a backend group exists and returns its ID.
     *
     * @since 0.12
     */
    private function checkBackendGroupByTitle(
        string $groupTitle,
    ): bool|int {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_groups');

        $backendGroupId = $queryBuilder
            ->select('*')
            ->from('be_groups')
            ->where(
                $queryBuilder->expr()->eq(
                    'title',
                    $queryBuilder->createNamedParameter($groupTitle, ParameterType::STRING),
                )
            )
            ->executeQuery()
            ->fetchOne();

        if ($backendGroupId > 0) {
            return $backendGroupId;
        }
        return false;
    }

    /**
     * @since 0.12
     */
    public function addVednorIdToBackendUser(
        int $vendorId,
    ): void {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('be_users');

        $connection->update(
            'be_users',
            ['usergroup' => $this->beUserGroup . ',' . (string)$vendorId],
            ['uid' => (int)$GLOBALS['BE_USER']->user['uid']]
        );
    }
}