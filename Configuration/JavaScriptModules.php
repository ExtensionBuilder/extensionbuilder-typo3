<?php

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@extensionbuilder/extensionbuildertypo3/' => [
            'path' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/',
            // Exclude files of the following folders from being import-mapped
            'exclude' => [
                'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Contrib/',
            ],
        ],
    ],
];
