<?php

declare(strict_types=1);

use TYPO3\CMS\RteCKEditor\Form\Resolver\RichTextNodeResolver;

defined('TYPO3') or die();

// Preset global registrieren
if (empty($GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['extensionbuilder_typo3'])) {
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['extensionbuilder_typo3'] = 'EXT:extensionbuilder_typo3/Configuration/RTE/Default.yaml';
}
