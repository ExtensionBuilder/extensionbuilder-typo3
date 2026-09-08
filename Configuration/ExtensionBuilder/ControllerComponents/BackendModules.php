<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

// Backend module

$components['beModules'] = [];
$components['beModules']['componentName'] = 'beModules';
$components['beModules']['title'] = 'Backend module';
$components['beModules']['group'] = 'backend';
$components['beModules']['disable'] = false;
$components['beModules']['docUrl'] = 
    'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/Index.html';
$components['beModules']['fieldsTabs'] = $standardFieldsTabs;
$components['beModules']['fields'] = $standardFields;

$components['beModules']['fields']['controllerType'] = ['type' => 'select', 'tab' => 'general'];
$components['beModules']['fields']['controllerType']['selects'] = [
    'extbaseController' => 'Extbase controller',
    'plainController' => 'Plain controller',
];

$components['beModules']['fields']['access'] = ['type' => 'select', 'tab' => 'general'];
$components['beModules']['fields']['access']['selects'] = [
    'user' => 'User',
    'admin' => 'Admin',
    'systemMaintainer' => 'System Maintainer',
];


// ToDo sub Array 
//$components['beModules']['fields']['position'] = ['type' => 'input', 'tab' => 'general'];

// $components['beModules']['fields']['path'] = ['type' => 'input', 'tab' => 'general']; // LLL-DE wird im core erzeugt
$components['beModules']['fields']['iconIdentifier'] = ['type' => 'input', 'tab' => 'general'];
$components['beModules']['fields']['labels'] = ['type' => 'input', 'tab' => 'general'];

//        'access' => 'admin',
//        'position' => ['before' => 'system'],
//        'path' => '/module/extensionBuilder',
//        'iconIdentifier' => 'mimetypes-x-content-form',
//        'labels' => $lllBase . ':title.extensionbuilder',

$components['beModules']['propertys']['modules'] = [];
$components['beModules']['propertys']['modules']['propertysName'] = 'modules';
$components['beModules']['propertys']['modules']['disable'] = false;
$components['beModules']['propertys']['modules']['title'] = 'Module';
$components['beModules']['propertys']['modules']['fieldsTabs'] = $standardFieldsTabs;
$components['beModules']['propertys']['modules']['fields'] = $standardFields;
$components['beModules']['propertys']['modules']['fields']['controllerActions'] = ['type' => 'lll', 'tab' => 'general'];
$components['beModules']['propertys']['modules']['fields']['language'] = ['type' => 'lll', 'tab' => 'general'];

//controller
$components['beModules']['propertys']['controller'] = [];
$components['beModules']['propertys']['controller']['propertysName'] = 'controller';
$components['beModules']['propertys']['controller']['disable'] = false;
$components['beModules']['propertys']['controller']['title'] = 'Controller';
$components['beModules']['propertys']['controller']['fieldsTabs'] = $standardFieldsTabs;
$components['beModules']['propertys']['controller']['fields'] = $standardFields;

$components['beModules']['propertys']['language'] = [];
$components['beModules']['propertys']['language']['propertysName'] = 'language';
$components['beModules']['propertys']['language']['disable'] = false;
$components['beModules']['propertys']['language']['title'] = 'Language';
$components['beModules']['propertys']['language']['fieldsTabs'] = $standardFieldsTabs;
$components['beModules']['propertys']['language']['fields'] = $standardFields;
