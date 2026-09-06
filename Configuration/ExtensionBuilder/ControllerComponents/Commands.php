<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

$components['commands'] = [];
$components['commands']['componentName'] = 'commands';
$components['commands']['title'] = 'Command';
$components['commands']['disable'] = false;
$components['commands']['docUrl'] = 
    'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/CommandControllers/Index.html';
$components['commands']['fieldsTabs'] = $standardFieldsTabs;
$components['commands']['fields'] = $standardFields;
$components['commands']['fields']['group'] = ['type' => 'input', 'tab' => 'general'];
$components['commands']['fields']['help'] = ['type' => 'input', 'tab' => 'general'];
$components['commands']['fields']['schedulable'] = ['type' => 'check', 'default' => true, 'tab' => 'general'];
$components['commands']['fields']['hidden'] = ['type' => 'check', 'tab' => 'general'];


// ToDo Array
// $components['commands']['fields']['alias'] = ['type' => 'check', 'tab' => 'general'];


$components['commands']['propertys'] = [];

$components['commands']['propertys']['arguments'] = [];
$components['commands']['propertys']['arguments']['propertysName'] = 'arguments';
$components['commands']['propertys']['arguments']['disable'] = false;
$components['commands']['propertys']['arguments']['title'] = 'Argument';
$components['commands']['propertys']['arguments']['fieldsTabs'] = $standardFieldsTabs;
$components['commands']['propertys']['arguments']['fieldsTabs']['code'] = [];
$components['commands']['propertys']['arguments']['fields'] = $standardFields;
$components['commands']['propertys']['arguments']['fields']['inputArgument']['selects'] = [
    'REQUIRED' => 'InputArgument::REQUIRED',
    'OPTIONAL' => 'InputArgument::OPTIONAL',
//    'IS_ARRAY' => 'InputArgument::IS_ARRAY',
];
$components['commands']['propertys']['arguments']['fields']['developerCode'] = ['type' => 'textarea', 'tab' => 'code'];


$components['commands']['propertys']['options'] = [];
$components['commands']['propertys']['options']['propertysName'] = 'options';
$components['commands']['propertys']['options']['disable'] = false;
$components['commands']['propertys']['options']['title'] = 'Option';
$components['commands']['propertys']['options']['fieldsTabs'] = $standardFieldsTabs;
$components['commands']['propertys']['options']['fieldsTabs']['code'] = [];
$components['commands']['propertys']['options']['fields'] = $standardFields;
$components['commands']['propertys']['options']['fields']['short'] = ['type' => 'input', 'tab' => 'general'];
$components['commands']['propertys']['options']['fields']['inputOption'] = ['type' => 'select', 'tab' => 'general'];
$components['commands']['propertys']['options']['fields']['inputOption']['selects'] = [
    'VALUE_NONE' => 'InputOption::VALUE_NONE',
    'VALUE_REQUIRED' => 'InputOption::VALUE_REQUIRED',
    'VALUE_OPTIONAL' => 'InputOption::VALUE_OPTIONAL',
//    'VALUE_IS_ARRAY' => 'InputOption::VALUE_IS_ARRAY',
];
