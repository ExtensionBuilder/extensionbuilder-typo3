<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

$lllPath = '.vendor';

// Add Vendor

$addTabs = [];
$addTabs['general'] = [];
$addTabs['address'] = [];
$addTabs['communication'] = [];
$addTabs['support'] = [];
$addTabs['access'] = [];

$add = [];
$add['vendorName'] = ['type' => 'input', 'tab' => 'general', 'row' => 'vendor', 'required' => true];
$add['vendorNamespace'] = ['type' => 'input', 'tab' => 'general', 'row' => 'vendor', 'required' => true];
$add['vendorComposerName'] = ['type' => 'input', 'tab' => 'general', 'row' => 'vendor', 'required' => true];
$add['company'] = ['type' => 'input', 'tab' => 'general'];
$add['description'] = ['type' => 'textarea', 'tab' => 'general'];

$add['address'] = ['type' => 'input', 'tab' => 'address'];
$add['postalcode'] = ['type' => 'input', 'tab' => 'address', 'row' => 'city'];
$add['city'] = ['type' => 'input', 'tab' => 'address', 'row' => 'city'];
$add['province'] = ['type' => 'input', 'tab' => 'address', 'row' => 'country'];
$add['country'] = ['type' => 'input', 'tab' => 'address', 'row' => 'country'];

$add['homepage'] = ['type' => 'input', 'tab' => 'communication', 'row' => 'web'];
$add['email'] = ['type' => 'input', 'tab' => 'communication', 'row' => 'web'];

$add['phone'] = ['type' => 'input', 'tab' => 'communication'];

$add['supportPhone'] = ['type' => 'input', 'tab' => 'support'];
$add['supportHomepage'] = ['type' => 'input', 'tab' => 'support'];
$add['supportIssues'] = ['type' => 'input', 'tab' => 'support'];
$add['supportWiki'] = ['type' => 'input', 'tab' => 'support'];
$add['supportDonation'] = ['type' => 'input', 'tab' => 'support'];



//funding composer
//keywords composer

$add['supportDonation'] = ['type' => 'input', 'tab' => 'support'];

$add['accessUsergroup'] = ['type' => 'input', 'tab' => 'access'];

// Edit Vendor

$editTabs =  $addTabs;
$editTabs['vendor'] = [];

$edit = $add;

unset($edit['vendorName']['required']);
unset($edit['vendorNamespace']['required']);
unset($edit['vendorComposerName']['required']);

$edit['vendorName']['readonly'] = true;
$edit['vendorNamespace']['readonly'] = true;
$edit['vendorComposerName']['readonly'] = true;

$edit['vendorId'] = ['type' => 'input', 'tab' => 'vendor', 'readonly' => true];

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