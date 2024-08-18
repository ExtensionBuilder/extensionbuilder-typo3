<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Setup;

use TYPO3\CMS\Core\Core\Environment;

class GlobalConfig
{

    public const EXT_NAME = 'extensionbuilder_typo3';
    public const VAR_EB = 'ExtensionBuilder' . DIRECTORY_SEPARATOR . 'TYPO3' . DIRECTORY_SEPARATOR;
    public const VAR_EB_CORE = 'ExtensionBuilderCore' . DIRECTORY_SEPARATOR . 'TYPO3' . DIRECTORY_SEPARATOR;
    public const REMOTE_API = 'extensionbuildcoretypo3';

    public static function setup(): array
    {
        $tmpReturn = [];

        return $tmpReturn;
	}

}