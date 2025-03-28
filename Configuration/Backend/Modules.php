<?php

use ExtensionBuilder\ExtensionbuilderTypo3\Controller;

return [
    'extensionBuilder' => [
        'access' => 'admin',
        'position' => ['before' => 'system'],
        'path' => '/module/extensionBuilder',
        'iconIdentifier' => 'mimetypes-x-content-form',
        'labels' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf',
    ],
    'extensionBuilder_typo3' => [
        'access' => 'admin',
        'parent' => 'extensionBuilder',
        'position' => ['before' => '*'],
        'path' => '/module/extensionBuilder/typo3',
        'iconIdentifier' => 'module-about',
        'labels' => 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.typo3.xlf',
        'extensionName' => 'extensionbuilder_typo3',

        'controllerActions' => [
            Controller\ExtensionController::class => [
                'list',
                'add',
                'edit',
//                'duplicate',
//                'rename',
                'delete',
                'build',
                'upload',
            ],
            Controller\CommandController::class => [
                'add',
                'edit',
                'delete',
            ],
            Controller\SchedulerController::class => [
                'add',
                'edit',
                'delete',
            ],

            Controller\ProjectController::class => [
                'list',
                'add',
                'edit',
                'delete',
                'addextension',
                'deleteextension',
            ],
            Controller\NotesAndIdeasController::class => [
                'list',
                'add',
                'edit',
                'delete',
            ],
            Controller\VendorController::class => [
                'list',
                'add',
                'edit',
//                'duplicate',
//                'rename',
                'delete',
                'importExampleVendor',
            ],
            Controller\DeveloperController::class => [
                'edit',
            ],
            Controller\ConfigurationController::class => [
                'edit',
            ],
            Controller\InfoController::class => [
                'show',
            ],
        ],
	],
];
