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





//    $components[''] = [];
//    $components['']['componentName'] = '';
//    $components['']['title'] = '';
//    $components['']['group'] = '';
//    $components['']['disable'] = false;
//    $components['']['fieldsTabs'] = $standardFieldsTabs;
//    $components['']['fields'] = $standardFields;
//    $components['']['propertys'] = [];


//    $components['']['propertys'][''] = [];
//    $components['']['propertys']['']['propertysName'] = '';
//    $components['']['propertys']['']['title'] = '';
//    $components['']['propertys']['']['group'] = ''; ToDO
//    $components['']['propertys']['']['disable'] = false;
//    $components['']['propertys']['']['select'] = false;
//    $components['']['propertys']['']['fieldsTabs'] = $standardFieldsTabs;
//    $components['']['propertys']['']['fields'] = $standardFields;


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

$files = scandir($ControllerComponetsPath);
$files = array_diff($files, ['.', '..']);
foreach ($files as $fileKey => $fileValue) {
    $extension = pathinfo($ControllerComponetsPath . $fileValue, PATHINFO_EXTENSION);
    if (strtolower($extension) === 'php') {
        require_once $ControllerComponetsPath . $fileValue;
    }
}

// Remove all inactive elements from the array and set lllPath
$componentsReturn = [];
$sortComponents = [];
foreach ($components ?? [] as $componentKey => $componentValue) {
    if (!($componentValue['disable'] ?? false)) {

        foreach ($componentValue['fields'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentValue['fields'][$key]['lllPath'] = $lllComponent;
		    }
        }
        foreach ($componentValue['fieldsTabs'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentValue['fieldsTabs'][$key]['lllPath'] = $lllPath;
		    }
        }
        foreach ($componentValue['propertyFields'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentValue['propertyFields'][$key]['lllPath'] = $lllPath;
		    }
        }
        foreach ($componentValue['propertyFieldsTabs'] ?? [] as $key => $value) {
            if (!($value['lllPath'] ?? false)) {
                $componentValue['propertyFieldsTabs'][$key]['lllPath'] = $lllProperty;
		    }
        }
        foreach ($componentValue['propertys'] ?? [] as $propertyKey => $propertyValue) {
            if ($propertyValue['disable'] ?? false) {
                unset($componentValue['propertys'][$propertyKey]);
            }
        }

        $group = $componentValue['group'] ?? 'general';
        if (!($sortComponents[$group] ?? false)) {
            $sortComponents[$group] = [];
        }
        $sortComponents[$group][$componentKey] = $componentValue;
    }
}

$componentsReturn  = [];
if ($sortComponents['models'] ?? false) {
    $componentsReturn = array_merge_recursive($componentsReturn ,$sortComponents['models']);
}
if ($sortComponents['backend'] ?? false) {
    $componentsReturn = array_merge_recursive($componentsReturn ,$sortComponents['backend']);
}
if ($sortComponents['extension'] ?? false) {
    $componentsReturn = array_merge_recursive($componentsReturn ,$sortComponents['extension']);
}
if ($sortComponents['general'] ?? false) {
    $componentsReturn = array_merge_recursive($componentsReturn ,$sortComponents['general']);
}

return $componentsReturn;