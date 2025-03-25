<?php

return [
    'dependencies' => [
        'backend',
		'core',
        'rte_ckeditor',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@extensionbuilder/typo3/' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/Backend/',

		 '@extensionbuilder/react/' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/React/',

//        '@extensionbuilder/test.js' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/test.js',
//        '@extensionbuilder/extensionbuilder_typo3/' => 'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/',
    ],
];
