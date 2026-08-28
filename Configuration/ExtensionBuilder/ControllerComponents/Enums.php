<?php

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.13
 */

$components['enums'] = [];
$components['enums']['componentName'] = 'enums';
$components['enums']['title'] = 'Enums';
$components['enums']['disable'] = false;
$components['enums']['controller'] = 'Component';
$components['enums']['add'] = 'ComponentAdd';
$components['enums']['edit'] = 'ComponentEdit';
$components['enums']['fieldsTabs'] = $standardFieldsTabs;
$components['enums']['fields'] = $standardFields;
$components['enums']['fields']['type'] = ['type' => 'select', 'tab' => 'general', 'showInList' => true];
$components['enums']['fields']['type']['selects'] = [
    'int' => 'Int',
    'string' => 'String',
];

$components['enums']['propertys'] = [];
$components['enums']['propertys']['enums'] = [];
$components['enums']['propertys']['enums']['propertysName'] = 'enums';
$components['enums']['propertys']['enums']['disable'] = false;
$components['enums']['propertys']['enums']['title'] = 'Enum';
$components['enums']['propertys']['enums']['controller'] = 'Property';
$components['enums']['propertys']['enums']['add'] = 'PropertyAdd';
$components['enums']['propertys']['enums']['edit'] = 'PropertyEdit';
$components['enums']['propertys']['enums']['fieldsTabs'] = $standardFieldsTabs;
$components['enums']['propertys']['enums']['fields'] = $standardFields;

// $standardFields
$components['enums']['propertys']['enums']['fields']['enum'] =
    ['type' => 'input', 'tab' => 'general', 'row' => 'enum', 'showInList' => true];
$components['enums']['propertys']['enums']['fields']['alias'] =
    ['type' => 'input', 'tab' => 'general', 'row' => 'enum', 'showInList' => true];

//$components['enums']['propertys']['enums']['fields']['language'] = ['type' => 'lll', 'tab' => 'general']; // ToDo Array field
