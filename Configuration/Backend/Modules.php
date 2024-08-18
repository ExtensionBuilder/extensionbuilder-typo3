<?php

return [
	'extensionbuilder' => [
		'position' => ['before' => 'system'],
		'access' => 'admin',
		'path' => '/module/extensionbuilder',
		'iconIdentifier' => 'mimetypes-x-content-form',
		'labels' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_moddules.xlf:mlang_tabs_tab',
	],
	'extensionbuilder_typo3_configuration' => [
		'parent' => 'extensionbuilder',
		'position' => ['before' => '*'],
		'access' => 'admin',
		'path' => '/module/extensionbuilder/typo3/overview',
		'iconIdentifier' => 'module-about',
		'labels' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang_moddules.xlf:mlang_labels_tablabel',

		'extensionName' => 'extensionbuilder_typo3',

        'routes' => [
            '_default' => [
                'target' => ExtensionBuilder\ExtensionbuilderTypo3\Controller\ExtensionModuleController::class.'::list',
            ],
        ],
		
	],
];
