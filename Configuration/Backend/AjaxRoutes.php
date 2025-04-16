<?php

use ExtensionBuilder\ExtensionbuilderTypo3\Controller;

return [
    'extensionbuilder_typo3' => [
        'path' => '/extensionbuildertypo3/build',
        'methods' => ['POST'],
        'target' => Controller\AjaxController::class . '::build',
    ],
];
