<?php

use ExtensionBuilder\ExtensionbuilderTypo3\Controller;

return [

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
    'extensionbuilder_typo3.extension.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/extension/duplicate',
        'target' => Controller\ExtensionModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.extension.rename' => [
        'path' => '/module/extensionbuilder/typo3/extension/rename',
        'target' => Controller\ExtensionModuleController::class.'::rename'
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


    'extensionbuilder_typo3.table.add' => [
        'path' => '/module/extensionbuilder/typo3/table/add',
        'target' => Controller\TableModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.table.edit' => [
        'path' => '/module/extensionbuilder/typo3/table/edit',
        'target' => Controller\TableModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.table.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/table/duplicate',
        'target' => Controller\TableModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.table.rename' => [
        'path' => '/module/extensionbuilder/typo3/table/rename',
        'target' => Controller\TableModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.table.delete' => [
        'path' => '/module/extensionbuilder/typo3/table/delete',
        'target' => Controller\TableModuleController::class.'::delete'
    ],


    'extensionbuilder_typo3.table.column.add' => [
        'path' => '/module/extensionbuilder/typo3/table/column/add',
        'target' => Controller\TableColumnModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.table.column.edit' => [
        'path' => '/module/extensionbuilder/typo3/table/column/edit',
        'target' => Controller\TableColumnModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.table.column.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/table/column/duplicate',
        'target' => Controller\TableColumnModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.table.column.rename' => [
        'path' => '/module/extensionbuilder/typo3/table/column/rename',
        'target' => Controller\TableColumnModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.table.column.delete' => [
        'path' => '/module/extensionbuilder/typo3/table/column/delete',
        'target' => Controller\TableColumnModuleController::class.'::delete'
    ],


    'extensionbuilder_typo3.backend.modules.add' => [
        'path' => '/module/extensionbuilder/typo3/backend/modules/add',
        'target' => Controller\BackendModulesModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.backend.modules.edit' => [
        'path' => '/module/extensionbuilder/typo3/backend/modules/edit',
        'target' => Controller\BackendModulesModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.backend.modules.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/backend/modules/duplicate',
        'target' => Controller\BackendModulesModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.backend.modules.rename' => [
        'path' => '/module/extensionbuilder/typo3/backend/modules/rename',
        'target' => Controller\BackendModulesModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.backend.modules.delete' => [
        'path' => '/module/extensionbuilder/typo3/backend/modules/delete',
        'target' => Controller\BackendModulesModuleController::class.'::delete'
    ],

    'extensionbuilder_typo3.backend.routes.add' => [
        'path' => '/module/extensionbuilder/typo3/backend/routes/add',
        'target' => Controller\BackendRoutesModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.backend.routes.edit' => [
        'path' => '/module/extensionbuilder/typo3/backend/routes/edit',
        'target' => Controller\BackendRoutesModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.backend.routes.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/backend/routes/duplicate',
        'target' => Controller\BackendRoutesModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.backend.routes.rename' => [
        'path' => '/module/extensionbuilder/typo3/backend/routes/rename',
        'target' => Controller\BackendRoutesModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.backend.routes.delete' => [
        'path' => '/module/extensionbuilder/typo3/backend/routes/delete',
        'target' => Controller\BackendRoutesModuleController::class.'::delete'
    ],



    'extensionbuilder_typo3.enumeration.add' => [
        'path' => '/module/extensionbuilder/typo3/enumeration/add',
        'target' => Controller\EnumerationModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.enumeration.edit' => [
        'path' => '/module/extensionbuilder/typo3/enumeration/edit',
        'target' => Controller\EnumerationModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.enumeration.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/enumeration/duplicate',
        'target' => Controller\EnumerationModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.enumeration.constant.rename' => [
        'path' => '/module/extensionbuilder/typo3/enumeration/constant/rename',
        'target' => Controller\EnumerationConstantModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.enumeration.constant.edit' => [
        'path' => '/module/extensionbuilder/typo3/enumeration/constant/edit',
        'target' => Controller\EnumerationConstantModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.enumeration.constant.delete' => [
        'path' => '/module/extensionbuilder/typo3/enumeration/constant/delete',
        'target' => Controller\EnumerationConstantModuleController::class.'::delete'
    ],


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
    'extensionbuilder_typo3.project.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/project/duplicate',
        'target' => Controller\ProjectModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.project.rename' => [
        'path' => '/module/extensionbuilder/typo3/project/rename',
        'target' => Controller\ProjectModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.project.delete' => [
        'path' => '/module/extensionbuilder/typo3/project/delete',
        'target' => Controller\ProjectModuleController::class.'::delete'
    ],
    'extensionbuilder_typo3.project.addextension' => [
        'path' => '/module/extensionbuilder/typo3/project/addextension',
        'target' => Controller\ProjectModuleController::class.'::addExtension'
    ],
    'extensionbuilder_typo3.project.deleteextension' => [
        'path' => '/module/extensionbuilder/typo3/project/deleteextension',
        'target' => Controller\ProjectModuleController::class.'::deleteExtension'
    ],


    'extensionbuilder_typo3.vendor' => [
        'path' => '/module/extensionbuilder/typo3/vendors',
        'target' => Controller\VendorModuleController::class.'::list'
    ],
    'extensionbuilder_typo3.vendor.add' => [
        'path' => '/module/extensionbuilder/typo3/vendor/add',
        'target' => Controller\VendorModuleController::class.'::add'
    ],
    'extensionbuilder_typo3.vendor.importExampleVendor' => [
        'path' => '/module/extensionbuilder/typo3/vendor/importExampleVendor',
        'target' => Controller\VendorModuleController::class.'::importExampleVendor'
    ],
    'extensionbuilder_typo3.vendor.edit' => [
        'path' => '/module/extensionbuilder/typo3/vendor/edit',
        'target' => Controller\VendorModuleController::class.'::edit'
    ],
    'extensionbuilder_typo3.vendor.duplicate' => [
        'path' => '/module/extensionbuilder/typo3/vendor/duplicate',
        'target' => Controller\VendorModuleController::class.'::duplicate'
    ],
    'extensionbuilder_typo3.vendor.rename' => [
        'path' => '/module/extensionbuilder/typo3/vendor/rename',
        'target' => Controller\VendorModuleController::class.'::rename'
    ],
    'extensionbuilder_typo3.vendor.delete' => [
        'path' => '/module/extensionbuilder/typo3/vendor/delete',
        'target' => Controller\VendorModuleController::class.'::delete'
    ],


    'extensionbuilder_typo3.developer' => [
        'path' => '/module/extensionbuilder/typo3/developer',
        'target' => Controller\DeveloperModuleController::class.'::developer'
    ],


    'extensionbuilder_typo3.configuration' => [
        'path' => '/module/extensionbuilder/typo3/configuration',
        'target' => Controller\ConfigurationModuleController::class.'::configuration'
    ],


    'extensionbuilder_typo3.info' => [
        'path' => '/module/extensionbuilder/typo3/info',
        'target' => Controller\InfoModuleController::class.'::info'
    ],

];