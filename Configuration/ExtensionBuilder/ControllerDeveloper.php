<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

$lllPath = '.developer';

// Add Developer

$addTabs = [];

$add = [];

// Edit Developer

$editTabs =  $addTabs;
$editTabs['general'] = [];
$editTabs['current'] = [];
$editTabs['build'] = [];
$editTabs['developer'] = [];

$edit = $add;
$edit['author'] = ['type' => 'input', 'tab' => 'general', 'required' => true];
$edit['author_email'] = ['type' => 'input', 'tab' => 'general'];
$edit['author_company'] = ['type' => 'input', 'tab' => 'general'];
$edit['description'] = ['type' => 'textarea', 'tab' => 'general'];

$edit['currentProject'] = ['array' => ['typo3'], 'type' => 'select', 'selectKey' => 'projects', 'tab' => 'current', 'row' => 'current'];
$edit['currentVendor']  = ['array' => ['typo3'], 'type' => 'select', 'selectKey' => 'vendors',  'tab' => 'current', 'row' => 'current'];

$edit['cacheFlush'] = ['array' => ['typo3'], 'type' => 'check', 'tab' => 'build', 'row' => 'build'];
$edit['cacheWarmup'] = ['array' => ['typo3'], 'type' => 'check', 'tab' => 'build', 'row' => 'build'];
$edit['dumpAutoload'] = ['array' => ['typo3'], 'type' => 'check', 'tab' => 'build', 'row' => 'build'];
$edit['databaseStructure'] = ['array' => ['typo3'], 'type' => 'check', 'tab' => 'build', 'row' => 'build'];

$edit['developerId'] = ['type' => 'input', 'tab' => 'developer', 'readonly' => true];

//

foreach ($addTabs as $key => $value) {
    if (!($value['lllPath'] ?? false)) { 
        $addTabs[$key]['lllPath'] = $lllPath;
    }
}
foreach ($add as $key => $value) {
    if (!($value['lllPath'] ?? false)) {
        $add[$key]['lllPath'] = $lllPath;
    }
}

foreach ($editTabs as $key => $value) {
    if (!($value['lllPath'] ?? false)) {
        $editTabs[$key]['lllPath'] = $lllPath;
    }
}
foreach ($edit as $key => $value) {
    if (!($value['lllPath'] ?? false)) {
        $edit[$key]['lllPath'] = $lllPath;
    }
}

$configuration = [
    'fieldsAddTabs' => $addTabs,
    'fieldsAdd' => $add,
    'fieldsEditTabs' => $editTabs,
    'fieldsEdit' => $edit,
];

return $configuration;