<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Extension Builder for TYPO3',
    'description' => ' - ',
	'category' => 'module',
	'author' => 'Stephan Sellner',
	'author_email' => 'contact@extension-builder.dev',
	'author_company' => 'extsnsion-builder.dev',
	'state' => 'beta',
	'version' => '0.4.7',
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
