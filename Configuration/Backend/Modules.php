<?php

return [
    'jsFiles' => [
        'EXT:extensionbuilder_typo3/Resources/Public/JavaScript/ModalXXX.js',
    ],
	'extensionBuilder' => [
		'position' => ['before' => 'system'],
		'access' => 'admin',
		'path' => '/module/extensionBuilder',
		'iconIdentifier' => 'mimetypes-x-content-form',
		'labels' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:module_tablabel',
	],
	'extensionBuilder_typo3' => [
		'parent' => 'extensionBuilder',
		'position' => ['before' => '*'],
		'access' => 'admin',
		'path' => '/module/extensionBuilder/typo3',
		'iconIdentifier' => 'module-about',
		'labels' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf:module_namelabel',

		'extensionName' => 'extensionbuilder_typo3',

        'routes' => [
            '_default' => [
                'target' => ExtensionBuilder\ExtensionbuilderTypo3\Controller\ExtensionModuleController::class.'::list',
            ],
        ],
		
	],
];