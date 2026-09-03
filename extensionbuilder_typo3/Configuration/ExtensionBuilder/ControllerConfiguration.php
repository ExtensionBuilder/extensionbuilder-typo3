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

$lllPath = '.configuration';

// Add Configuration

$addTabs = [];

$add = [];

// Edit Configuration

$editTabs =  $addTabs;
$editTabs['general'] = [];
$editTabs['professional']  = ['array' => ['typo3'], 'showBy' => 'proKeyActive'];
$editTabs['URL/API'] = ['showBy' => 'proKeyActive'];
$editTabs['system'] = [];

$edit = $add;

// General
$edit['developerRepository'] =
    ['array' => ['typo3'], 'type' => 'input', 'tab' => 'general', 'row' => 'repository', 'readonly' => true, 'default' => 'ExtensionBuilder'];
$edit['composerRepository'] =
    ['array' => ['typo3'], 'type' => 'input', 'tab' => 'general', 'row' => 'repository', 'readonly' => true, 'default' => 'packages'];

// URL/API
$edit['builderUrl'] =
    ['array' => ['typo3'], 'type' => 'input', 'tab' => 'URL/API', 'row' => 'builder', 'readonly' => true,
    'default' => 'https://typo3.extension-builder.dev'];
$edit['builderApi'] =
    ['array' => ['typo3'], 'type' => 'input', 'tab' => 'URL/API', 'row' => 'builder', 'readonly' => true,
    'default' => '/api/v1/extensionbuildcoretypo3'];
$edit['builderDevUrl'] =
    ['array' => ['typo3'], 'type' => 'input', 'tab' => 'URL/API', 'row' => 'dev', 'readonly' => true,
    'default' => 'https://development.extension-builder.dev'];
$edit['builderDevApi'] = ['array' => ['typo3'], 'type' => 'input', 'tab' => 'URL/API', 'row' => 'dev', 'readonly' => true,
    'default' => '/api/v1/extensionbuildcoretypo3'];

// professional
$edit['professionalKey'] = ['array' => ['typo3'], 'type' => 'input', 'tab' => 'professional', 'row' => 'key', 'readonly' => false];
$edit['professionalId'] = ['array' => ['typo3'], 'type' => 'input', 'tab' => 'professional', 'row' => 'key', 'readonly' => false];
$edit['buildLocal'] = ['array' => ['typo3'], 'type' => 'check', 'tab' => 'professional', 'row' => 'build', 'showBy' => 'isProKey'];
$edit['buildDev'] = ['array' => ['typo3'], 'type' => 'check', 'tab' => 'professional', 'row' => 'build', 'showBy' => 'isProKey'];

// System ID
$edit['systemId'] = ['type' => 'input', 'tab' => 'system', 'readonly' => true];


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
] ;

return $configuration;