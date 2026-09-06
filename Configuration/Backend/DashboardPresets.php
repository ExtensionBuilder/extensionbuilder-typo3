<?php

declare(strict_types=1);

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

return [
    'ebTypo3Preset' => [
        'title' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.widgets.xlf:title',
        'description' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.widgets.xlf:description',
        'iconIdentifier' => 'eb-logo-png',
        'showInWizard' => true,
        'defaultWidgets' => [
            'extensionbuilder.typo3.overview'
        ],
    ],
];