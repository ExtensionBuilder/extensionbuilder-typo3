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


$lllPath = '.project';

// Add Project

$addTabs = [];
$addTabs['general'] = [];
$addTabs['description'] = [];
$addTabs['toDo'] = [];
$addTabs['issue'] = [];


$add = [];
$add['name'] = ['type' => 'input', 'tab' => 'general'];
$add['scope'] = ['type' => 'select', 'showInlist' => true, 'tab' => 'general'];
$add['scope']['selects'] = [
//        'developer' => 'Developer project',
//        'vendor' => 'Vendor project',
        'global' => 'Global project',
];
$add['vendorName'] = ['type' => 'select', 'tab' => 'general']; // ToDo selects

$add['ebDisable'] = ['type' => 'check', 'showInlist' => true, 'tab' => 'general'];
$add['ebDevDescription'] = ['type' => 'textarea', 'tab' => 'description'];
$add['ebDevTodo'] = ['type' => 'textarea', 'tab' => 'toDo'];
$add['ebDevIssue'] = ['type' => 'textarea', 'tab' => 'issue'];


// Edit Project

$editTabs =  $addTabs;

$edit = $add;
unset($edit['name']);

$edit['scope']['readonly'] = true;
$edit['vendorName']['readonly'] = true;

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