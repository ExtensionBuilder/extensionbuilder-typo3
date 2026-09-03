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

// Extbase-Plugins, bei Backend-Modulen oder bei Ajax-Endpunkten
// eID
// typNum
// route

$components['ajaxs'] = [];
$components['ajaxs']['componentName'] = 'ajaxs';
$components['ajaxs']['title'] = 'AJAX';
$components['ajaxs']['disable'] = true;
$components['ajaxs']['controller'] = 'Component';
$components['ajaxs']['add'] = 'ComponentAdd';
$components['ajaxs']['edit'] = 'ComponentEdit';
$components['ajaxs']['fieldsTabs'] = $standardFieldsTabs;
$components['ajaxs']['fields'] = $standardFields;

// access

$components['ajaxs']['fields']['auth'] = ['type' => 'select', 'default' => true,  'tab' => 'general'];
$components['ajaxs']['fields']['auth']['selects'] = [
    'none' => 'None',
    'frontend' => 'Frontend',
    'backend' => 'Backend',
];

$components['ajaxs']['fields']['access'] = ['type' => 'select', 'default' => true,  'tab' => 'general'];
$components['ajaxs']['fields']['access']['selects'] = [
    'public' => 'Public',
    'user' => 'User',
    'admin' => 'Admin',
];

$components['ajaxs']['fields']['type'] = ['type' => 'select', 'default' => true,  'tab' => 'general'];
$components['ajaxs']['fields']['type']['selects'] = [
    'route' => 'route',
    'typNum' => 'typNum',
    'eID' => 'eID',
];

$components['ajaxs']['fields']['route'] = ['type' => 'input', 'default' => true,  'tab' => 'general'];
$components['ajaxs']['fields']['typeNum'] = ['type' => 'input', 'default' => true,  'tab' => 'general'];
$components['ajaxs']['fields']['eID'] = ['type' => 'input', 'default' => true,  'tab' => 'general'];

$components['ajaxs']['propertys']['requests'] = [];
$components['ajaxs']['propertys']['requests']['propertysName'] = 'requests';
$components['ajaxs']['propertys']['requests']['title'] = 'Request';
$components['ajaxs']['propertys']['requests']['disable'] = false;
$components['ajaxs']['propertys']['requests']['select'] = false;
$components['ajaxs']['propertys']['requests']['controller'] = 'Property';
$components['ajaxs']['propertys']['requests']['add'] = 'PropertyAdd';
$components['ajaxs']['propertys']['requests']['edit'] = 'PropertyEdit';
$components['ajaxs']['propertys']['requests']['fieldsTabs'] = $standardFieldsTabs;
$components['ajaxs']['propertys']['requests']['fields'] = $standardFields;

// 'type'

$components['ajaxs']['propertys']['responses'] = [];
$components['ajaxs']['propertys']['responses']['propertysName'] = 'responses';
$components['ajaxs']['propertys']['responses']['title'] = 'Response';
$components['ajaxs']['propertys']['responses']['disable'] = false;
$components['ajaxs']['propertys']['responses']['select'] = false;
$components['ajaxs']['propertys']['responses']['controller'] = 'Property';
$components['ajaxs']['propertys']['responses']['add'] = 'PropertyAdd';
$components['ajaxs']['propertys']['responses']['edit'] = 'PropertyEdit';
$components['ajaxs']['propertys']['responses']['fieldsTabs'] = $standardFieldsTabs;
$components['ajaxs']['propertys']['responses']['fields'] = $standardFields;
