<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Setup;

use TYPO3\CMS\Core\Core\Environment;

class Config
{

    public const BUILDERURI = 'https://typo3.extension-builder.dev/';

//    public const EXT_NAME = 'extensionbuilder_typo3';
    public const VAR_EB = 'ExtensionBuilder' . DIRECTORY_SEPARATOR . 'TYPO3' . DIRECTORY_SEPARATOR;
    public const VAR_EB_CORE = 'ExtensionBuilderCore' . DIRECTORY_SEPARATOR . 'TYPO3' . DIRECTORY_SEPARATOR;
    public const REMOTE_API = 'extensionbuildcoretypo3';

    public static function setupRemove(): array
    {
        $tmpReturn = [];

        return $tmpReturn;
	}

}