<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Extension Builder for TYPO3',
    'description' => 'RC 1 - ',
    'category' => 'module',
    'author' => 'Stephan Franz Sellner',
    'author_email' => 'contact@extension-builder.dev',
    'author_company' => 'extension-builder.dev',
    'state' => 'beta',
    'version' => '0.5.142',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4',
        ],
    ],
	'autoload' => [
		'psr-4' => [
			'ExtensionBuilder\\ExtensionbuilderTypo3\\' => 'Classes',
		],
	],
];