<?php

declare(strict_types=1);

use ExtensionBuilder\ExtensionBuilderTypo3\Controller;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */

return [
    'extensionbuilder_getFieldsValues' => [
        'inheritAccessFromModule' => 'extensionbuilder_typo3',
        'path' => '/extensionbuilder/getFieldsValues',
        'target' => Controller\ExtensionBuilderAjaxController::class . '::getFieldsValuesAction',
    ],
    'extensionbuilder_checkName' => [
        'inheritAccessFromModule' => 'extensionbuilder_typo3',
        'path' => '/extensionbuilder/checkName',
        'target' => Controller\ExtensionBuilderAjaxController::class . '::checkNameAction',
    ],
    'extensionbuilder_typo3_readDevCode' => [
        'inheritAccessFromModule' => 'extensionbuilder_typo3',
        'path' => '/extensionbuilder/typo3/readDevCode',
        'target' => Controller\ExtensionBuilderAjaxController::class . '::readDevCodeAction',
    ],
    'extensionbuilder_typo3_writeDevCode' => [
        'inheritAccessFromModule' => 'extensionbuilder_typo3',
        'path' => '/extensionbuilder/typo3/writeDevCode',
        'target' => Controller\ExtensionBuilderAjaxController::class . '::writeDevCodeAction',
    ],
    'extensionbuilder_typo3_build' => [
        'inheritAccessFromModule' => 'extensionbuilder_typo3',
        'path' => '/extensionbuilder/typo3/build',
        'target' => Controller\ExtensionBuilderAjaxController::class . '::buildAction',
    ],
];