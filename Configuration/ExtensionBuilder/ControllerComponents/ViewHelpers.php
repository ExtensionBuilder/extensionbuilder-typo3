<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

$components['viewHelpers'] = [];
$components['viewHelpers']['componentName'] = 'viewHelpers';
$components['viewHelpers']['title'] = 'ViewHelper';
$components['viewHelpers']['path'] = 'Classes' . DIRECTORY_SEPARATOR . 'ViewHelpers'. DIRECTORY_SEPARATOR ;
$components['viewHelpers']['disable'] = true;
$components['viewHelpers']['docUrl'] = 
    'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Fluid/DevelopCustomViewhelper.html';
$components['viewHelpers']['fieldsTabs'] =$standardFieldsTabs;
$components['viewHelpers']['fields'] = $standardFields;

$components['viewHelpers']['fields']['escapeOutput'] = ['type' => 'check', 'tab' => 'general'];
$components['viewHelpers']['fields']['prefix'] = ['type' => 'input', 'tab' => 'general'];

$components['viewHelpers']['propertys'] = [];
$components['viewHelpers']['propertys']['arguments'] = [];
$components['viewHelpers']['propertys']['arguments']['propertysName'] = 'arguments';
//5555
$components['viewHelpers']['propertys']['arguments']['path'] = '';
$components['viewHelpers']['propertys']['arguments']['fileEnd'] = 'ViewHelpers';

$components['viewHelpers']['propertys']['arguments']['disable'] = false;
$components['viewHelpers']['propertys']['arguments']['title'] = 'Argument';
$components['viewHelpers']['propertys']['arguments']['fieldsTabs'] = $standardFieldsTabs;
$components['viewHelpers']['propertys']['arguments']['fields'] = [];
$components['viewHelpers']['propertys']['arguments']['fields']['type'] = ['type' => 'input', 'tab' => 'general'];
$components['viewHelpers']['propertys']['arguments']['fields']['required'] = ['type' => 'check', 'tab' => 'general'];
$components['viewHelpers']['propertys']['arguments']['fields']['defaultValue'] = ['type' => 'input', 'tab' => 'general'];
$components['viewHelpers']['propertys']['arguments']['fields']['escape'] = ['type' => 'check', 'tab' => 'general'];

$components['viewHelpers']['propertys']['arguments']['fields']['devCode'] = ['type' => 'devCode', 'tab' => 'general'];
