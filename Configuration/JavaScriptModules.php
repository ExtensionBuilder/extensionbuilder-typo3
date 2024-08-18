<?php

return [
    'dependencies' => [
        'backend',
        'core',
    ],
//    'tags' => [
//        'backend.module',
//    ],
    'imports' => [
        '@extensionbuilder/test.js' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/test.js',
//        '@extensionbuilder/extensionbuilder_typo3/' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/',
    ],
];
