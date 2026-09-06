<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Extension Builder for TYPO3',
    'description' => 'The Extension Builder for TYPO3, supporting version 13 and above',
    'category' => 'module',
    'author' => 'Stephan Franz Sellner',
    'author_email' => 'contact@extension-builder.dev',
    'author_company' => 'extension-builder.dev',
    'state' => 'stable',
    'version' => '0.14.262',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.5.99',
            'typo3' => '13.4.0-14.9.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'ExtensionBuilder\\ExtensionBuilderTypo3\\' => 'Classes/',
        ],
    ],
];