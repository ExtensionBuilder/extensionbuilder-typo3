<?php

declare(strict_types=1);

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

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@extensionbuilder/typo3/' => [
            'path' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/',
            'exclude' => [
                'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Contrib/',
            ],
        ],
    ],
];
