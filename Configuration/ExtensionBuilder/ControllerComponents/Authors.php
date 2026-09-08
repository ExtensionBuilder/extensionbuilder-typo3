<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

$components['authors']['componentName'] = 'authors';
$components['authors']['title'] = 'Authors';
$components['authors']['disable'] = false;
$components['authors']['group'] = 'extension';
$components['authors']['fieldsTabs'] = $standardFieldsTabs;
unset($components['authors']['fieldsTabs']['todo']);
unset($components['authors']['fieldsTabs']['issue']);

$components['authors']['fields'] = [];
$components['authors']['fields']['company'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['role'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['email'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['homepage'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['description'] = ['type' => 'propertys', 'tab' => 'description'];
// No properties available
$components['authors']['propertys'] = [];
