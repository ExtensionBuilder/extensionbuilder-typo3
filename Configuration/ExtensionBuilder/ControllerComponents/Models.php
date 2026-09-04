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

// ToDo Rename to  FrontendModels

$components['models'] = [];
$components['models']['componentName'] = 'models';
$components['models']['title'] = 'Model';
$components['models']['disable'] = false;
$components['models']['controller'] = 'Component';
$components['models']['add'] = 'ComponentAdd';
$components['models']['edit'] = 'ComponentEdit';
$components['models']['fieldsTabs'] = $standardFieldsTabs;
$components['models']['fields'] = $standardFields;

$components['models']['fields']['plugin'] = ['type' => 'check', 'default' => true, 'tab' => 'general'];
$components['models']['fields']['pluginDescription'] = ['type' => 'input','tab' => 'general'];
$components['models']['fields']['pluginGroup'] = ['type' => 'input','tab' => 'general'];

$components['models']['propertys'] = [];
$components['models']['propertys']['columns'] = [];
$components['models']['propertys']['columns']['propertysName'] = 'columns';
$components['models']['propertys']['columns']['title'] = 'Column';
$components['models']['propertys']['columns']['disable'] = false;
$components['models']['propertys']['columns']['controller'] = 'Property';
$components['models']['propertys']['columns']['add'] = 'PropertyAdd';
$components['models']['propertys']['columns']['edit'] = 'PropertyEdit';
$components['models']['propertys']['columns']['fieldsTabs'] = $standardFieldsTabs;
$components['models']['propertys']['columns']['fieldsTabs']['tab'] = [];
$components['models']['propertys']['columns']['fields'] = $standardFields;
$components['models']['propertys']['columns']['fields']['type'] = ['type' => 'select', 'tab' => 'general'];
$components['models']['propertys']['columns']['fields']['type']['selects'] = &$this->tcaTypesSelects;
$components['models']['propertys']['columns']['fields']['size'] = ['type' => 'input', 'tab' => 'general'];
$components['models']['propertys']['columns']['fields']['readOnly'] = ['type' => 'check', 'tab' => 'general'];
$components['models']['propertys']['columns']['fields']['label'] = ['type' => 'check', 'tab' => 'general'];
$components['models']['propertys']['columns']['fields']['backendLabe'] = ['type' => 'check', 'tab' => 'general'];


$components['models']['propertys']['columns']['fields']['tabId'] = ['type' => 'select', 'selectKey' => 'tab', 'tab' => 'tab'];


$components['models']['propertys']['controllers'] = [];
$components['models']['propertys']['controllers']['propertysName'] = 'controllers';
$components['models']['propertys']['controllers']['title'] = 'Controller';
$components['models']['propertys']['controllers']['disable'] = false;
$components['models']['propertys']['controllers']['controller'] = 'Property';
$components['models']['propertys']['controllers']['add'] = 'PropertyAdd';
$components['models']['propertys']['controllers']['edit'] = 'PropertyEdit';

// ToDo 9999
$components['models']['propertys']['controllers']['fieldsTabs'] = $standardFieldsTabs;
$components['models']['propertys']['controllers']['fieldsTabs']['views'] = [];

$components['models']['propertys']['controllers']['fields'] = $standardFields;

$components['models']['propertys']['controllers']['fields']['new'] = ['type' => 'check', 'tab' => 'views'];

// Ajaxs for fontend models

//                "ajax": {
//                    "getUniqueData": {
//                        "typeNum": 5055,
//                        "findBy": "uid",
//                        "access": "private"
//                    },
//                    "getSearchData": {
//                        "typeNum": 5056,
//                        "findBy": "uid",
//                        "access": "private"
//                    },
//                    "getGpsData": {
//                        "typeNum": 5057,
//                        "access": "private",
//                        "findBy": {
//                            "road": "road",
//                            "housenumber": "housenumber",
//                            "postalcode": "postalcode",
//                            "city": "city",
//                            "country": "country"
//                        },
//                        "result": {
//                            "gps_data_available": "dataAvailable",
//                            "gps_latitude": "latitude",
//                            "gps_longitude": "longitude"
//                        }
//                    }
//                },

$components['models']['propertys']['ajaxs'] = [];
$components['models']['propertys']['ajaxs']['propertysName'] = 'ajaxs';
$components['models']['propertys']['ajaxs']['title'] = 'Ajax';
$components['models']['propertys']['ajaxs']['disable'] = false;
$components['models']['propertys']['ajaxs']['controller'] = 'Property';
$components['models']['propertys']['ajaxs']['add'] = 'PropertyAdd';
$components['models']['propertys']['ajaxs']['edit'] = 'PropertyEdit';

$components['models']['propertys']['ajaxs']['fieldsTabs'] = $standardFieldsTabs;
$components['models']['propertys']['ajaxs']['fieldsTabs']['views'] = [];

$components['models']['propertys']['ajaxs']['fields'] = $standardFields;

$components['models']['propertys']['ajaxs']['fields']['label'] = ['type' => 'input','tab' => 'general'];
$components['models']['propertys']['ajaxs']['fields']['typeNum'] = ['type' => 'input','tab' => 'general'];
$components['models']['propertys']['ajaxs']['fields']['new'] = ['type' => 'input','tab' => 'general'];




$components['models']['propertys']['tabs'] = [];
$components['models']['propertys']['tabs']['propertysName'] = 'tabs';
$components['models']['propertys']['tabs']['title'] = 'Tab';
$components['models']['propertys']['tabs']['disable'] = false;
$components['models']['propertys']['tabs']['select'] = false;
$components['models']['propertys']['tabs']['controller'] = 'Property';
$components['models']['propertys']['tabs']['add'] = 'PropertyAdd';
$components['models']['propertys']['tabs']['edit'] = 'PropertyEdit';
$components['models']['propertys']['tabs']['fieldsTabs'] = $standardFieldsTabs;
$components['models']['propertys']['tabs']['fields'] = $standardFields;
$components['models']['propertys']['tabs']['fields']['icon'] = ['type' => 'input', 'tab' => 'general'];
$components['models']['propertys']['tabs']['fields']['language'] = ['type' => 'lll', 'tab' => 'general'];

$components['models']['propertys']['palettes'] = [];
$components['models']['propertys']['palettes']['propertysName'] = 'palettes';
$components['models']['propertys']['palettes']['title'] = 'Palette';
$components['models']['propertys']['palettes']['disable'] = false;
$components['models']['propertys']['palettes']['select'] = false;
$components['models']['propertys']['palettes']['controller'] = 'Property';
$components['models']['propertys']['palettes']['add'] = 'PropertyAdd';
$components['models']['propertys']['palettes']['edit'] = 'PropertyEdit';
$components['models']['propertys']['palettes']['fieldsTabs'] = $standardFieldsTabs;
$components['models']['propertys']['palettes']['fields'] = $standardFields;

//    $propertyFields = [];
//    $propertyFields['type'] = [
//        'type' => 'select',
//         'select' => [
//            'input' => 'Input',
//            'uuid' => 'UUID',
//         ],
//    ];
