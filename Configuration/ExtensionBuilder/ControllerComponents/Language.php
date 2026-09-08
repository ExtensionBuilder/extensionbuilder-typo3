<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

$components['language'] = [];
$components['language']['componentName'] = 'language';
$components['language']['title'] = 'Language (Beta no Code)';
$components['language']['disable'] = false;
$components['language']['fieldsTabs'] = $standardFieldsTabs;
$components['language']['fields'] = $standardFields;
unset($components['language']['fieldsTabs']['todo']);
unset($components['language']['fieldsTabs']['issue']);

$components['language']['fields'] = [];
$components['language']['fields']['pluginDescription'] = ['type' => 'input','tab' => 'general'];
$components['language']['fields']['pluginGroup'] = ['type' => 'input','tab' => 'general'];

$components['language']['propertys'] = [];
$components['language']['propertys']['translation'] = [];
$components['language']['propertys']['translation']['propertysName'] = 'translation';
$components['language']['propertys']['translation']['disable'] = false;
$components['language']['propertys']['translation']['title'] = 'Translation';
$components['language']['propertys']['translation']['fieldsTabs'] = $standardFieldsTabs;
$components['language']['propertys']['translation']['fieldsTabs']['code'] = [];
$components['language']['propertys']['translation']['fields'] = $standardFields;
$components['language']['propertys']['translation']['fields']['propertyNameX'] = ['type' => 'select', 'showInList' => true, 'tab' => 'general'];
$components['language']['propertys']['translation']['fields']['propertyNameX']['selects'] = [
    'de' => 'de',
    'dk' => 'dk',
];
$components['language']['propertys']['translation']['fields']['translation'] = ['type' => 'input', 'tab' => 'general'];
