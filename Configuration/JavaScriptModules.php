<?php

declare(strict_types=1);

/**
 * @extensionbuilderCoreMajorVersion 1
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