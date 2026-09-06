<?php

declare(strict_types=1);

use ExtensionBuilder\ExtensionBuilderTypo3\Controller;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */

$lllBase = 'LLL:EXT:extensionbuilder_typo3/Resources/Private/Language/locallang.xlf';

return [
    'extensionbuilder' => [
        'access' => 'user',
        'position' => ['before' => 'tools'],
        'path' => '/module/extensionbuilder',
        'iconIdentifier' => 'eb-logo-png',
        'labels' => ['title' => $lllBase . ':title.extensionbuilder'],
    ],
    'extensionbuilder_typo3' => [
        'access' => 'user',
        'parent' => 'extensionbuilder',
        'position' => ['before' => '*'],
        'path' => '/module/extensionbuilder/typo3',
        'iconIdentifier' => 'module-about',
        'labels' => ['title' => $lllBase . ':title.typo3'],
        'extensionName' => 'extensionbuilder_typo3',
        'controllerActions' => [
            Controller\ExtensionController::class => [
                'list',
                'add',
                'edit',
                'delete',
                'upload',
            ],
            Controller\ComponentController::class => [
                'add',
                'edit',
                'delete',
            ],
            Controller\PropertyController::class => [
                'add',
                'edit',
                'delete',
            ],
            Controller\ExtensionInfoController::class => [
                'info',
            ],
            Controller\ProjectController::class => [
                'list',
                'add',
                'edit',
                'delete',
                'addExtension',
                'deleteExtension',
            ],
            Controller\VendorController::class => [
                'list',
                'add',
                'edit',
                'delete',
            ],
            Controller\DeveloperController::class => [
                'edit',
            ],
            Controller\ConfigurationController::class => [
                'edit',
            ],
            Controller\DeveloperHubController::class => [
                'show',
            ],
            Controller\InfoController::class => [
                'show',
            ],
        ],
	],
];