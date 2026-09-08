<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

$components['includeModels'] = [];
$components['includeModels']['disable'] = true;

$components['includeModels']['propertys'] = [];
$components['includeModels']['propertys']['columns'] = [];
$components['includeModels']['propertys']['columns']['propertysName'] = 'columns';
$components['includeModels']['propertys']['columns']['title'] = 'Column';
$components['includeModels']['propertys']['columns']['disable'] = false;
$components['includeModels']['propertys']['columns']['fieldsTabs'] = $standardFieldsTabs;
$components['includeModels']['propertys']['columns']['fieldsTabs']['tab'] = [];
$components['includeModels']['propertys']['columns']['fields'] = $standardFields;
$components['includeModels']['propertys']['columns']['fields']['type'] = ['type' => 'select', 'tab' => 'general'];
$components['includeModels']['propertys']['columns']['fields']['type']['selects'] = &$this->tcaTypesSelects;
$components['includeModels']['propertys']['columns']['fields']['size'] = ['type' => 'input', 'tab' => 'general'];
$components['includeModels']['propertys']['columns']['fields']['readOnly'] = ['type' => 'check', 'tab' => 'general'];
$components['includeModels']['propertys']['columns']['fields']['label'] = ['type' => 'check', 'tab' => 'general'];
$components['includeModels']['propertys']['columns']['fields']['backendLabe'] = ['type' => 'check', 'tab' => 'general'];

$components['includeModels']['propertys']['columns']['fields']['tabId'] = ['type' => 'select', 'selectKey' => 'tab', 'tab' => 'tab'];


$components['includeModels']['propertys']['tabs'] = [];
$components['includeModels']['propertys']['tabs']['propertysName'] = 'tabs';
$components['includeModels']['propertys']['tabs']['title'] = 'Tab';
$components['includeModels']['propertys']['tabs']['disable'] = true;
$components['includeModels']['propertys']['tabs']['select'] = false;
$components['includeModels']['propertys']['tabs']['fieldsTabs'] = $standardFieldsTabs;
$components['includeModels']['propertys']['tabs']['fields'] = $standardFields;
$components['includeModels']['propertys']['tabs']['fields']['icon'] = ['type' => 'input', 'tab' => 'general'];
$components['includeModels']['propertys']['tabs']['fields']['language'] = ['type' => 'lll', 'tab' => 'general'];

$components['includeModels']['propertys']['palettes'] = [];
$components['includeModels']['propertys']['palettes']['propertysName'] = 'palettes';
$components['includeModels']['propertys']['palettes']['title'] = 'Palette';
$components['includeModels']['propertys']['palettes']['disable'] = true;
$components['includeModels']['propertys']['palettes']['select'] = false;
$components['includeModels']['propertys']['palettes']['fieldsTabs'] = $standardFieldsTabs;
$components['includeModels']['propertys']['palettes']['fields'] = $standardFields;

//    $propertyFields = [];
//    $propertyFields['type'] = [
//        'type' => 'select',
//         'select' => [
//            'input' => 'Input',
//            'uuid' => 'UUID',
//         ],
//    ];
