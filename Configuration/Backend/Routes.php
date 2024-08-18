<?php

use ExtensionBuilder\ExtensionbuilderTypo3\Controller;

return [

    // Extension
    'extensionbuilder_typo3.extension' => [
        'path' => '/module/extensionbuilder/typo3/extensions',
        'target' => Controller\ExtensionModuleController::class.'::list'
    ],
    'extensionbuilder_typo3.extension.add' => [
        'path' => '/module/extensionbuilder/typo3/extension/add',
        'target' => Controller\ExtensionModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.extension.edit' => [
        'path' => '/module/extensionbuilder/typo3/extension/edit',
        'target' => Controller\ExtensionModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.extension.delete' => [
        'path' => '/module/extensionbuilder/typo3/extension/delete',
        'target' => Controller\ExtensionModuleController::class.'::delete'
    ],
    'extensionbuilder_typo3.extension.build' => [
        'path' => '/module/extensionbuilder/typo3/extension/build',
        'target' => Controller\ExtensionModuleController::class.'::build'
    ],
    'extensionbuilder_typo3.extension.upload' => [
        'path' => '/module/eextensionbuilder/typo3/extension/upload',
        'target' => Controller\ExtensionModuleController::class.'::upload'
    ],
	
    // Extension - Table
    'extensionbuilder_typo3.extension.table.add' => [
        'path' => '/module/extensionbuilder/typo3/extension/table/add',
        'target' => Controller\ExtensionTableModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.extension.table.edit' => [
        'path' => '/module/extensionbuilder/typo3/extension/table/edit',
        'target' => Controller\ExtensionTableModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.extension.table.delete' => [
        'path' => '/module/extensionbuilder/typo3/extension/table/delete',
        'target' => Controller\ExtensionTableModuleController::class.'::delete'
    ],

    // Extension - Enumeration
    'extensionbuilder_typo3.extension.enumeration.add' => [
        'path' => '/module/extensionbuilder/typo3/extension/enumeration/add',
        'target' => Controller\ExtensionEnumerationModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.extension.enumeration.edit' => [
        'path' => '/module/extensionbuilder/typo3/extension/enumeration/edit',
        'target' => Controller\ExtensionEnumerationModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.extension.enumeration.delete' => [
        'path' => '/module/extensionbuilder/typo3/extension/enumeration/delete',
        'target' => Controller\ExtensionEnumerationModuleController::class.'::delete'
    ],
    'extensionbuilder_typo3.extension.enumeration.constant.add' => [
        'path' => '/module/extensionbuilder/typo3/extension/enumeration/constant/add',
        'target' => Controller\ExtensionEnumerationConstantModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.extension.enumeration.constant.edit' => [
        'path' => '/module/extensionbuilder/typo3/extension/enumeration/constant/edit',
        'target' => Controller\ExtensionEnumerationConstantModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.extension.enumeration.constant.delete' => [
        'path' => '/module/extensionbuilder/typo3/extension/enumeration/constant/delete',
        'target' => Controller\ExtensionEnumerationConstantModuleController::class.'::delete'
    ],

    // Project
    'extensionbuilder_typo3.project' => [
        'path' => '/module/extensionbuilder/typo3/projects',
        'target' => Controller\ProjectModuleController::class.'::list'
    ],
    'extensionbuilder_typo3.project.add' => [
        'path' => '/module/extensionbuilder/typo3/project/add',
        'target' => Controller\ProjectModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.project.edit' => [
        'path' => '/module/extensionbuilder/typo3/project/edit',
        'target' => Controller\ProjectModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.project.delete' => [
        'path' => '/module/extensionbuilder/typo3/project/delete',
        'target' => Controller\ProjectModuleController::class.'::delete'
    ],
	
    // Vendor
    'extensionbuilder_typo3.vendor' => [
        'path' => '/module/extensionbuilder/typo3/vendors',
        'target' => Controller\VendorModuleController::class.'::list'
    ],
    'extensionbuilder_typo3.vendor.add' => [
        'path' => '/module/extensionbuilder/typo3/vendor/add',
        'target' => Controller\VendorModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.vendor.edit' => [
        'path' => '/module/extensionbuilder/typo3/vendor/edit',
        'target' => Controller\VendorModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.vendor.delete' => [
        'path' => '/module/extensionbuilder/typo3/vendor/delete',
        'target' => Controller\VendorModuleController::class.'::delete'
    ],

    // Developer
    'extensionbuilder_typo3.developer' => [
        'path' => '/module/extensionbuilder/typo3/developer',
        'target' => Controller\DeveloperModuleController::class.'::developer'
    ],

    // Configuration
    'extensionbuilder_typo3.configuration' => [
        'path' => '/module/extensionbuilder/typo3/configuration',
        'target' => Controller\ConfigurationModuleController::class.'::configuration'
    ],

    // Getstarted
    'extensionbuilder_typo3.getstarted' => [
        'path' => '/module/extensionbuilder/typo3/getstarted',
        'target' => Controller\GetstartedModuleController::class.'::getstarted'
    ],

    // Info
    'extensionbuilder_typo3.info' => [
        'path' => '/module/extensionbuilder/typo3/info',
        'target' => Controller\InfoModuleController::class.'::info'
    ],

];