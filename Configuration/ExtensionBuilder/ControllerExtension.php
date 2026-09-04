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

$lllPath = '.extension';

//

$addTabs = [];
$addTabs['general'] = [];

$add = [];
$add['type'] = ['type' => 'select', 'selectKey' => 'type', 'tab' => 'general', 'disable' => true];

$add['vendorName'] =
    [
        'type' => 'select', 'selectKey' => 'vendors', 'tab' => 'general', 'row' => 'vendor', 'required' => true,
        'copy' => ['config' => 'vendors', 'key' => 'vendorName', 'fields' => ['vendorNamespace', 'vendorNameComposer']]
    ];
$add['vendorNamespace'] = ['type' => 'input', 'tab' => 'general', 'row' => 'vendor', 'readonly' => true];
$add['vendorNameComposer'] = ['type' => 'input', 'tab' => 'general', 'row' => 'vendor', 'readonly' => true];

$add['extensionName'] = ['type' => 'input', 'tab' => 'general', 'row' => 'extension', 'required' => true];
$add['extensionNamespace'] = ['type' => 'input', 'tab' => 'general', 'row' => 'extension', 'required' => true];
$add['extensionNameComposer'] = ['type' => 'input', 'tab' => 'general', 'row' => 'extension', 'required' => true];

$add['description'] = ['type' => 'input', 'tab' => 'general'];

$add['category'] = ['type' => 'select', 'selectKey' => 'category', 'tab' => 'general', 'row' => 'category'];

$add['state'] = ['type' => 'select', 'selectKey' => 'state', 'tab' => 'general', 'row' => 'category'];

$add['versionMajor'] = ['type' => 'number', 'tab' => 'general', 'row' => 'version', 'default' => 0];
$add['versionMinor'] = ['type' => 'number', 'tab' => 'general', 'row' => 'version', 'default' => 1];
$add['versionRevision'] = ['type' => 'number', 'tab' => 'general', 'row' => 'version', 'default' => 0];

$add['12'] = ['array' => ['typoVersion'], 'type' => 'check', 'tab' => 'general', 'row' => 'typo3', 'default' => false];
$add['13'] = ['array' => ['typoVersion'], 'type' => 'check', 'tab' => 'general', 'row' => 'typo3', 'default' => true];
$add['14'] = ['array' => ['typoVersion'], 'type' => 'check', 'tab' => 'general', 'row' => 'typo3', 'default' => true];
$add['15'] = ['array' => ['typoVersion'], 'type' => 'check', 'tab' => 'general', 'row' => 'typo3', 'default' => false];

//

$editTabs = $addTabs;
$editTabs['description'] = [];
$editTabs['toDo'] = [];
$editTabs['issue'] = [];
$editTabs['build'] = [];
$editTabs['studio'] = [];
$editTabs['constraints'] = [];
$editTabs['support'] = [];
$editTabs['suggest'] = [];
$editTabs['author'] = [];
$editTabs['license'] = [];

$editTabs['extensionInfo'] = [];

//

$edit = $add;

$edit['vendorName']['type'] = 'input';
$edit['vendorName']['readonly'] = true;
unset($edit['vendorName']['required']);
unset($edit['vendorName']['selectKey']);

unset($edit['vendorNameComposer']['required']);

$edit['extensionName']['readonly'] = true;
unset($edit['extensionName']['required']);

$edit['extensionNameComposer']['readonly'] = true;
unset($edit['extensionNameComposer']['required']);

$edit['extensionNamespace']['readonly'] = true;
unset($edit['extensionNamespace']['required']);

$edit['type']['type'] = 'input';
$edit['type']['readonly'] = true;
unset($edit['type']['selectKey']);

$edit['developmentMode'] =
    ['array' => ['ebBuild'], 'type' => 'check', 'tab' => 'build', 'row' => 'development', 'default' => false];
$edit['developmentAiInfo'] =
    ['array' => ['ebBuild'], 'type' => 'check', 'tab' => 'build', 'row' => 'development', 'default' => false];

$edit['lllSmall'] = ['array' => ['ebBuild'], 'type' => 'check', 'tab' => 'build', 'row' => 'lll', 'default' => false];
$edit['fluidDebug'] = ['array' => ['ebBuild'], 'type' => 'check', 'tab' => 'build', 'row' => 'lll', 'default' => false];

$edit['agentsMd'] =
    ['type' => 'textarea', 'tab' => 'build'];


$edit['constraints'] = ['type' => 'array', 'tab' => 'constraints'];

$edit['authors'] = ['type' => 'array', 'tab' => 'author'];


// ToDo type url array. support
$edit['supportPhone'] = ['type' => 'input', 'tab' => 'support'];
$edit['supportHomepage'] = ['type' => 'input', 'tab' => 'support'];
$edit['supportIssues'] = ['type' => 'input', 'tab' => 'support'];
$edit['supportWiki'] = ['type' => 'input', 'tab' => 'support'];
$edit['supportDonation'] = ['type' => 'input', 'tab' => 'support'];

$edit['license'] = ['type' => 'select', 'selectKey' => 'license', 'tab' => 'license'];
$edit['ownLicenseText'] = ['type' => 'textarea', 'tab' => 'license'];

$edit['ebDevDescriptionShort'] =
    ['array' => ['wbInfo'], 'type' => 'input', 'tab' => 'description', 'showInList' => true];
$edit['ebDevDescription'] =
    ['array' => ['ebInfo'], 'type' => 'textarea', 'tab' => 'description'];
$edit['ebDevTodo'] =
    ['array' => ['ebInfo'], 'type' => 'textarea', 'tab' => 'toDo'];
$edit['ebDevIssue'] =
    ['array' => ['ebInfo'], 'type' => 'textarea', 'tab' => 'issue'];

$edit['ebDevStudioStage'] =
    ['array' => ['ebStudio'], 'type' => 'check', 'default' => false, 'tab' => 'studio'];
$edit['ebDevStudioStageUrl'] =
    ['array' => ['ebStudio'], 'type' => 'input', 'tab' => 'studio'];
$edit['ebDevStudioStageSecret'] =
    ['array' => ['ebStudio'], 'type' => 'input', 'tab' => 'studio'];

$edit['ebDevExtensionId'] =
    ['type' => 'input', 'tab' => 'extensionInfo', 'group' => 'extensionInfo', 'readonly' => true];


//

$selects = [];
$selects['type'] = [
    'extension' => 'Extension',
    'sitepackage' => 'Site Package',
];
$selects['category'] = [
    'no' => 'Please select',
    'be'  => 'BE',
    'module' => 'Module',
    'fe' => 'FE',
    'plugin' => 'Plugin',
    'misc' => 'Misc',
    'services' => 'Services',
    'templates' => 'Templates',
    'example' => 'Example',
    'doc' => 'Doc',
    'distribution' => 'Distribution'
];
$selects['state'] = [
    'no' => 'Please select',
    'alpha' => 'Alpha',
    'beta' => 'Beta',
    'stable' => 'Stable',
    'experimental' => 'Experimental',
    'test' => 'Test',
    'obsolete' => 'Obsolete',
    'excludeFromUpdates' => 'Exclude From Updates'
];
$selects['license'] = [
    'no' => 'Please select',
    'own' => 'Own license',
    'gpl20only' => 'GPL-2.0-only',
    'gpl20orlater' => 'GPL-2.0-or-later'
];

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
    'selects' => $selects,
];

return $configuration;