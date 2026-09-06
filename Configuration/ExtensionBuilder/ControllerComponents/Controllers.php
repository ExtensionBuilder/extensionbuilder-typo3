<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

// ToDo Obsolate -> FrondenModels?

$components['controllers'] = [];
$components['controllers']['componentName'] = 'controllers';
$components['controllers']['title'] = 'Controller';
$components['controllers']['disable'] = true;
$components['controllers']['fieldsTabs'] = $standardFieldsTabs;
$components['controllers']['fields'] = $standardFields;
$components['controllers']['fields']['type'] = ['type' => 'select', 'default' => true,  'tab' => 'general'];
$components['controllers']['fields']['type']['selects'] = [
    'none' => 'None',
    'frontend' => 'Frontend controller',
    'backend' => 'Backend controller',
    'ajax' => 'Ajax controller',
];
$components['controllers']['fields']['extbaseController'] = ['type' => 'select', 'default' => true,  'tab' => 'general'];
$components['controllers']['fields']['extbaseController']['selects'] = [
    'extbase' => 'Extbase',
    'noextbase' => 'No Extbase',
];

$components['controllers']['propertys'] = [];

$components['controllers']['propertys']['action'] = [];
$components['controllers']['propertys']['action']['propertysName'] = 'action';
$components['controllers']['propertys']['action']['title'] = 'Action';
$components['controllers']['propertys']['action']['disable'] = false;
$components['controllers']['propertys']['action']['select'] = false;
$components['controllers']['propertys']['action']['fieldsTabs'] = $standardFieldsTabs;
$components['controllers']['propertys']['action']['fields'] = $standardFields;

//    $components['controllers']['propertys'][''] = [];
//    $components['controllers']['propertys']['']['propertysName'] = '';
//    $components['controllers']['propertys']['']['title'] = '';
//    $components['controllers']['propertys']['']['disable'] = false;
//    $components['controllers']['propertys']['']['select'] = false;
//    $components['controllers']['propertys']['']['edit'] = 'PropertyEdit';
//    $components['controllers']['propertys']['']['fieldsTabs'] = $standardFieldsTabs;
//    $components['controllers']['propertys']['']['fields'] = $standardFields;
