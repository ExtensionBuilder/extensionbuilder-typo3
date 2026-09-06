<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

/**
 *
 * The properties of the field are controlled by the following parameters.
 * 
 * type       => <string>    required    input|textarea|check|select |   lll  array
 * array      => <array>     optional    
 * default    => <mix>       optional    
 * showInList => <bool>      optional    
 * tab        => <string>    required    
 * row        => <string>    optional    
 * disable    => <bool>      optional    
 * readonly   => <bool>      optional    
 * required   => <bool>      optional    
 * copy       => <array>     optional    
 *   config   => <string>    required    
 *   key      => <string>    required    
 *   fields   => <arra>      required    
 * 
 *
 *  
 * 
 */

$ControllerComponetsPath = $extensionConfigurationPath . 'ControllerComponents' . DIRECTORY_SEPARATOR;

$extensionConfiguration = [];

$lllComponent = '.component';
$lllProperty = '.property';

$standardFieldsTabs = [];
$standardFieldsTabs['general'] = [];
$standardFieldsTabs['description'] = [];
$standardFieldsTabs['todo'] = [];
$standardFieldsTabs['issue'] = [];

$standardFields = [];
$standardFields['ebDisable'] = ['type' => 'check', 'default' => false, 'tab' => 'general', 'showInList' => true]; 
$standardFields['ebDevDescriptionShort'] = ['type' => 'input', 'tab' => 'description', 'showInList' => true];
$standardFields['ebDevDescription'] = ['type' => 'textarea', 'tab' => 'description'];
$standardFields['ebDevTodo'] = ['type' => 'textarea', 'tab' => 'todo'];
$standardFields['ebDevIssue'] = ['type' => 'textarea', 'tab' => 'issue'];

// --------------------------------------------------



//    $components[''] = [];
//    $components['']['componentName'] = '';
//    $components['']['title'] = '';
//    $components['']['disable'] = false;
//    $components['']['fieldsTabs'] = $standardFieldsTabs;
//    $components['']['fields'] = $standardFields;
//    $components['']['propertys'] = [];


//    $components['']['propertys'][''] = [];
//    $components['']['propertys']['']['propertysName'] = '';
//    $components['']['propertys']['']['title'] = '';
//    $components['']['propertys']['']['disable'] = false;
//    $components['']['propertys']['']['select'] = false;
//    $components['']['propertys']['']['fieldsTabs'] = $standardFieldsTabs;
//    $components['']['propertys']['']['fields'] = $standardFields;


// --------------------------------------------------

// Site Package components

$sitePackageFields = [];
$sitePackageFields['basePackage'] = [
    'type' => 'select',
    'select' => [
        'bootstrapPackage' => 'Bootstrap Package',
        'fluidStyledContent' => 'Fluid Styled Content',
    ],
];

$sitePackageFields['title2'] = ['type' => 'text', 'tab' => 'general'];

$sitePackagePropertys = [];

$components['sitePackage'] = [
    'componentName' => 'sitePackage',
    'title' => 'SitePackageX',
    'disable' => true,
    'docUrl' => '',
    'controller' => 'Component',
    'add' => 'ComponentAdd',
    'edit' => 'ComponentEdit',
    'fields' => $sitePackageFields,
    'propertys' => $sitePackagePropertys,
];


// Extension components


// Addon Extensions

// ToDo move to extsenion config
$components['constraints'] = [];
$components['constraints']['componentName'] = 'constraints';
$components['constraints']['title'] = 'Constraints';
$components['constraints']['disable'] = true;
$components['constraints'][''] = true;
$components['constraints']['fieldsTabs'] = $standardFieldsTabs;

$components['constraints']['fields'] = [];
$components['constraints']['fields']['componentName'] = ['type' => 'select', 'tab' => 'general'];
$components['constraints']['fields']['componentName']['selects'] = &$this->localExtensions;
$components['constraints']['fields']['componentName']['tab'] = 'general';
$components['constraints']['fields']['componentName']['required'] = true;

$components['constraints']['fields']['constraint'] = ['type' => 'select', 'showInList' => true, 'tab' => 'general'];
$components['constraints']['fields']['constraint']['selects'] = [
    'depends' => 'Depends',
    'conflicts' => 'Conflicts',
    'suggests' => 'Suggests',
];

$components['constraints']['fields']['version'] = ['type' => 'input', 'showInList' => true, 'tab' => 'general'];

$components['constraints']['fields']['description'] = ['type' => 'input', 'showInList' => true, 'tab' => 'general'];
$components['constraints']['fields']['todo'] = ['type' => 'textarea', 'tab' => 'todo'];
$components['constraints']['fields']['issue'] = ['type' => 'textarea', 'tab' => 'issue'];

$components['constraints']['propertys'] = [];


// ToDo move to extsenion config
$components['authors']['componentName'] = 'authors';
$components['authors']['title'] = 'Authors';
$components['authors']['disable'] = true;
$components['authors']['fieldsTabs'] = $standardFieldsTabs;
unset($components['authors']['fieldsTabs']['todo']);
unset($components['authors']['fieldsTabs']['issue']);

$components['authors']['fields'] = [];
//$components['authors']['fields']['name'] = ['type' => 'input', 'showInList' => true, 'tab' => 'general'];
$components['authors']['fields']['company'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['role'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['email'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['homepage'] = ['type' => 'input', 'tab' => 'general'];
$components['authors']['fields']['description'] = ['type' => 'input', 'tab' => 'general'];

$components['authors']['propertys'] = [];

$files = scandir($ControllerComponetsPath);
$files = array_diff($files, ['.', '..']);
foreach ($files as $fileKey => $fileValue) {
    require_once $ControllerComponetsPath . $fileValue;
}

// Remove all inactive elements from the array and set lllPath
$componentsReturn = [];
foreach ($components ?? [] as $componentName => $componentData) {
    if (!($componentData['disable'] ?? false)) {
        foreach ($componentData['fields'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentData['fields'][$key]['lllPath'] = $lllComponent;
		    }
        }
        foreach ($componentData['fieldsTabs'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentData['fieldsTabs'][$key]['lllPath'] = $lllPath;
		    }
        }
        foreach ($componentData['propertyFields'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentData['propertyFields'][$key]['lllPath'] = $lllPath;
		    }
        }
        foreach ($componentData['propertyFieldsTabs'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentData['propertyFieldsTabs'][$key]['lllPath'] = $lllProperty;
		    }
        }
        foreach ($componentData['propertys'] ?? [] as $propertyName => $propertyData) {
            if ($propertyData['disable'] ?? false) {
                unset($componentData['propertys'][$propertyName]);
            }
        }
        $componentsReturn[$componentName] = $componentData;
    }
}

return $componentsReturn;