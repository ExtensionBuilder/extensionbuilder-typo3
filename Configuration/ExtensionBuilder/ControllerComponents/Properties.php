<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

//fields
    // Property

//            "dateTime": {
//                "priority": 20,
//                "target": "DateTime",
//                "sources": "string,integer,array"
//            }

$components['propertys'] = [];
$components['propertys']['componentName'] = 'propertys';
$components['propertys']['title'] = 'Property';

$components['propertys']['path'] = 'Classes' . DIRECTORY_SEPARATOR . 'Property'. DIRECTORY_SEPARATOR ;

$components['propertys']['disable'] = false;
$components['propertys']['fieldsTabs'] = $standardFieldsTabs;
$components['propertys']['fields'] = $standardFields;
$components['propertys']['propertys'] = [];
$components['propertys']['propertys']['typeConverter'] = [];
$components['propertys']['propertys']['typeConverter']['propertysName'] = 'typeConverter';

$components['propertys']['propertys']['typeConverter']['path'] = 'TypeConverter' . DIRECTORY_SEPARATOR;
$components['propertys']['propertys']['typeConverter']['fileEnd'] = 'Converter';

$components['propertys']['propertys']['typeConverter']['disable'] = false;
$components['propertys']['propertys']['typeConverter']['title'] = 'Type Converter';
$components['propertys']['propertys']['typeConverter']['fieldsTabs'] = $standardFieldsTabs;
$components['propertys']['propertys']['typeConverter']['fields'] = [];
$components['propertys']['propertys']['typeConverter']['fields']['priority'] = ['type' => 'number', 'tab' => 'general'];
$components['propertys']['propertys']['typeConverter']['fields']['target'] = ['type' => 'input', 'tab' => 'general'];
$components['propertys']['propertys']['typeConverter']['fields']['sources'] = ['type' => 'input', 'tab' => 'general'];
$components['propertys']['propertys']['typeConverter']['fields']['devCode'] = ['type' => 'devCode', 'tab' => 'general'];
