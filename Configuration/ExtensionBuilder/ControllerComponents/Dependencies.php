<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

$components['dependencies'] = [];
$components['dependencies']['componentName'] = 'dependencies';
$components['dependencies']['title'] = 'Dependencies';
$components['dependencies']['group'] = 'extension';
$components['dependencies']['disable'] = false;
$components['dependencies']['fieldsTabs'] = $standardFieldsTabs;
unset($components['dependencies']['fieldsTabs']['todo']);
unset($components['dependencies']['fieldsTabs']['issue']);

$components['dependencies']['fields'] = [];
$components['dependencies']['fields']['componentName'] = ['type' => 'select', 'tab' => 'general'];
$components['dependencies']['fields']['componentName']['selects'] = &$this->localExtensions;
$components['dependencies']['fields']['componentName']['tab'] = 'general';
$components['dependencies']['fields']['componentName']['required'] = true;
$components['dependencies']['fields']['constraint'] = ['type' => 'select', 'showInList' => true, 'tab' => 'general'];
$components['dependencies']['fields']['constraint']['selects'] = [
    'require' => 'Require',
    'suggests' => 'Suggests',
];
$components['dependencies']['fields']['version'] = ['type' => 'input', 'showInList' => true, 'tab' => 'general'];
$components['dependencies']['fields']['description'] = ['type' => 'textarea', 'showInList' => true, 'tab' => 'description'];
// No properties available
$components['dependencies']['propertys'] = [];
