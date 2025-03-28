<?php

use ExtensionBuilder\ExtensionbuilderTypo3\Controller;

return [

    'extensionbuilder_typo3.extension.edit' => [
        'path' => '/module/extensionBuilder/typo3/extension/edit',
        'target' => Controller\ExtensionController::class.'::editAction'
    ],
    'extensionbuilder_typo3.extension.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/extension/duplicate',
        'target' => Controller\ExtensionController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.extension.rename' => [
        'path' => '/module/extensionBuilder/typo3/extension/rename',
        'target' => Controller\ExtensionController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.extension.delete' => [
        'path' => '/module/extensionBuilder/typo3/extension/delete',
        'target' => Controller\ExtensionController::class.'::deleteAction'
    ],
    'extensionbuilder_typo3.extension.upload' => [
        'path' => '/module/eextensionBuilder/typo3/extension/upload',
        'target' => Controller\ExtensionController::class.'::uploadAction'
    ],
    'extensionbuilder_typo3.extension.build' => [
        'path' => '/module/extensionBuilder/typo3/extension/build',
        'target' => Controller\ExtensionController::class.'::buildAction'
    ],


    'extensionbuilder_typo3.contentElement.edit' => [
        'path' => '/module/extensionBuilder/typo3/contentElement/edit',
        'target' => Controller\ContentElementController::class.'::editAction'
    ],
    'extensionbuilder_typo3.contentElement.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/contentElement/duplicate',
        'target' => Controller\ContentElementController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.contentElement.rename' => [
        'path' => '/module/extensionBuilder/typo3/contentElement/rename',
        'target' => Controller\ContentElementController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.contentElement.delete' => [
        'path' => '/module/extensionBuilder/typo3/contentElement/delete',
        'target' => Controller\ContentElementController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.plugin.edit' => [
        'path' => '/module/extensionBuilder/typo3/plugin/edit',
        'target' => Controller\PluginController::class.'::editAction'
    ],
    'extensionbuilder_typo3.plugin.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/plugin/duplicate',
        'target' => Controller\PluginController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.plugin.rename' => [
        'path' => '/module/extensionBuilder/typo3/plugin/rename',
        'target' => Controller\PluginController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.plugin.delete' => [
        'path' => '/module/extensionBuilder/typo3/plugin/delete',
        'target' => Controller\PluginController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.scheduler.edit' => [
        'path' => '/module/extensionBuilder/typo3/scheduler/edit',
        'target' => Controller\SchedulerController::class.'::editAction'
    ],
    'extensionbuilder_typo3.scheduler.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/schedulern/duplicate',
        'target' => Controller\SchedulerController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.scheduler.rename' => [
        'path' => '/module/extensionBuilder/typo3/scheduler/rename',
        'target' => Controller\SchedulerController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.scheduler.delete' => [
        'path' => '/module/extensionBuilder/typo3/scheduler/delete',
        'target' => Controller\SchedulerController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.command.edit' => [
        'path' => '/module/extensionBuilder/typo3/command/edit',
        'target' => Controller\CommandController::class.'::editAction'
    ],
    'extensionbuilder_typo3.command.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/command/duplicate',
        'target' => Controller\CommandController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.command.rename' => [
        'path' => '/module/extensionBuilder/typo3/command/rename',
        'target' => Controller\CommandController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.command.delete' => [
        'path' => '/module/extensionBuilder/typo3/command/delete',
        'target' => Controller\CommandController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.viewHelper.edit' => [
        'path' => '/module/extensionBuilder/typo3/viewHelper/edit',
        'target' => Controller\ViewHelperController::class.'::editAction'
    ],
    'extensionbuilder_typo3.pviewHelper.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/viewHelper/duplicate',
        'target' => Controller\ViewHelperController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.viewHelper.rename' => [
        'path' => '/module/extensionBuilder/typo3/viewHelper/rename',
        'target' => Controller\ViewHelperController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.viewHelper.delete' => [
        'path' => '/module/extensionBuilder/typo3/viewHelper/delete',
        'target' => Controller\ViewHelperController::class.'::deleteAction'
    ],



    'extensionbuilder_typo3.table.edit' => [
        'path' => '/module/extensionBuilder/typo3/table/edit',
        'target' => Controller\TableController::class.'::editAction'
    ],
    'extensionbuilder_typo3.table.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/table/duplicate',
        'target' => Controller\TableController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.table.rename' => [
        'path' => '/module/extensionBuilder/typo3/table/rename',
        'target' => Controller\TableController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.table.delete' => [
        'path' => '/module/extensionBuilder/typo3/table/delete',
        'target' => Controller\TableController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.table.column.edit' => [
        'path' => '/module/extensionBuilder/typo3/table/column/edit',
        'target' => Controller\TableColumnController::class.'::editAction'
    ],
    'extensionbuilder_typo3.table.column.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/table/column/duplicate',
        'target' => Controller\TableColumnController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.table.column.rename' => [
        'path' => '/module/extensionBuilder/typo3/table/column/rename',
        'target' => Controller\TableColumnController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.table.column.delete' => [
        'path' => '/module/extensionBuilder/typo3/table/column/delete',
        'target' => Controller\TableColumnController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.backend.modules.edit' => [
        'path' => '/module/extensionBuilder/typo3/backend/modules/edit',
        'target' => Controller\BackendModulesController::class.'::editAction'
    ],
    'extensionbuilder_typo3.backend.modules.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/backend/modules/duplicate',
        'target' => Controller\BackendModulesController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.backend.modules.rename' => [
        'path' => '/module/extensionBuilder/typo3/backend/modules/rename',
        'target' => Controller\BackendModulesController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.backend.modules.delete' => [
        'path' => '/module/extensionBuilder/typo3/backend/modules/delete',
        'target' => Controller\BackendModulesController::class.'::deleteAction'
    ],

    'extensionbuilder_typo3.backend.routes.edit' => [
        'path' => '/module/extensionBuilder/typo3/backend/routes/edit',
        'target' => Controller\BackendRoutesController::class.'::editAction'
    ],
    'extensionbuilder_typo3.backend.routes.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/backend/routes/duplicate',
        'target' => Controller\BackendRoutesController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.backend.routes.rename' => [
        'path' => '/module/extensionBuilder/typo3/backend/routes/rename',
        'target' => Controller\BackendRoutesController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.backend.routes.delete' => [
        'path' => '/module/extensionBuilder/typo3/backend/routes/delete',
        'target' => Controller\BackendRoutesController::class.'::deleteAction'
    ],



    'extensionbuilder_typo3.enumeration.edit' => [
        'path' => '/module/extensionBuilder/typo3/enumeration/edit',
        'target' => Controller\EnumerationController::class.'::editAction'
    ],
    'extensionbuilder_typo3.enumeration.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/enumeration/duplicate',
        'target' => Controller\EnumerationController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.enumeration.constant.rename' => [
        'path' => '/module/extensionBuilder/typo3/enumeration/constant/rename',
        'target' => Controller\EnumerationConstantController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.enumeration.constant.edit' => [
        'path' => '/module/extensionBuilder/typo3/enumeration/constant/edit',
        'target' => Controller\EnumerationConstantController::class.'::editAction'
    ],
    'extensionbuilder_typo3.enumeration.constant.delete' => [
        'path' => '/module/extensionBuilder/typo3/enumeration/constant/delete',
        'target' => Controller\EnumerationConstantController::class.'::deleteAction'
    ],


    'extensionbuilder_typo3.project.edit' => [
        'path' => '/module/extensionBuilder/typo3/project/edit',
        'target' => Controller\ProjectController::class.'::editAction'
    ],
    'extensionbuilder_typo3.project.delete' => [
        'path' => '/module/extensionBuilder/typo3/project/delete',
        'target' => Controller\ProjectController::class.'::deleteAction'
    ],
    'extensionbuilder_typo3.project.addextension' => [
        'path' => '/module/extensionBuilder/typo3/project/addextension',
        'target' => Controller\ProjectController::class.'::addExtensionAction'
    ],
    'extensionbuilder_typo3.project.deleteextension' => [
        'path' => '/module/extensionBuilder/typo3/project/deleteextension',
        'target' => Controller\ProjectController::class.'::deleteExtensionAction'
    ],


    'extensionbuilder_typo3.vendor.edit' => [
        'path' => '/module/extensionBuilder/typo3/vendor/edit',
        'target' => Controller\VendorController::class.'::editAction'
    ],
    'extensionbuilder_typo3.vendor.duplicate' => [
        'path' => '/module/extensionBuilder/typo3/vendor/duplicate',
        'target' => Controller\VendorController::class.'::duplicateAction'
    ],
    'extensionbuilder_typo3.vendor.rename' => [
        'path' => '/module/extensionBuilder/typo3/vendor/rename',
        'target' => Controller\VendorController::class.'::renameAction'
    ],
    'extensionbuilder_typo3.vendor.delete' => [
        'path' => '/module/extensionBuilder/typo3/vendor/delete',
        'target' => Controller\VendorController::class.'::deleteAction'
    ],



    'extensionbuilder_typo3.notesandideas.add' => [
        'path' => '/module/extensionBuilder/typo3/notesandideas/add',
        'target' => Controller\NotesAndIdeasController::class.'::addAction'
    ],
    'extensionbuilder_typo3.notesandideas.edit' => [
        'path' => '/module/extensionBuilder/typo3/notesandideas/edit',
        'target' => Controller\NotesAndIdeasController::class.'::editAction'
    ],
    'extensionbuilder_typo3.notesandideas.delete' => [
        'path' => '/module/extensionBuilder/typo3/notesandideas/delete',
        'target' => Controller\NotesAndIdeasController::class.'::deleteAction'
    ],

];