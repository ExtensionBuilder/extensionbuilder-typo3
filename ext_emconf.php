<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Extension Builder for TYPO3',
    'description' => 'The Extension Builder for TYPO3, supporting version 12 and above',
    'category' => 'module',
    'author' => 'Stephan Franz Sellner',
    'author_email' => 'contact@extension-builder.dev',
    'author_company' => 'extension-builder.dev',
    'state' => 'beta',
    'version' => '0.6.153',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.9.99',
        ],
    ],
	'autoload' => [
		'psr-4' => [
			'ExtensionBuilder\\ExtensionbuilderTypo3\\' => 'Classes',
		],
	],
];