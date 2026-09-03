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

$components['contentSecurityPolicys'] = [];
$components['contentSecurityPolicys']['componentName'] = 'contentSecurityPolicys';
$components['contentSecurityPolicys']['docUrl'] = 'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/ContentSecurityPolicy/Index.html';
$components['contentSecurityPolicys']['askai'] = true;

$components['contentSecurityPolicys']['title'] = 'ContentSecurityPolicy';
$components['contentSecurityPolicys']['disable'] = false;
$components['contentSecurityPolicys']['controller'] = 'Component';
$components['contentSecurityPolicys']['add'] = 'ComponentAdd';
$components['contentSecurityPolicys']['edit'] = 'ComponentEdit';
$components['contentSecurityPolicys']['max'] = 2;
$components['contentSecurityPolicys']['fieldsTabs'] = $standardFieldsTabs;
$components['contentSecurityPolicys']['fields'] = $standardFields;
$components['contentSecurityPolicys']['fields']['componentName'] = ['type' => 'select', 'tab' => 'general'];
$components['contentSecurityPolicys']['fields']['componentName']['selects'] = [
    'frontend' => 'Frontend',
    'backend' => 'Backend',
];
$components['contentSecurityPolicys']['fields']['aipromt'] = ['type' => 'textarea', 'row' => '10', 'tab' => 'general'];

$components['contentSecurityPolicys']['propertys'] = [];

$components['contentSecurityPolicys']['propertys']['mutation'] = [];
$components['contentSecurityPolicys']['propertys']['mutation']['propertysName'] = 'mutation';
$components['contentSecurityPolicys']['propertys']['mutation']['title'] = 'Mutation';
$components['contentSecurityPolicys']['propertys']['mutation']['disable'] = false;
$components['contentSecurityPolicys']['propertys']['mutation']['controller'] = 'Property';
$components['contentSecurityPolicys']['propertys']['mutation']['add'] = 'PropertyAdd';
$components['contentSecurityPolicys']['propertys']['mutation']['edit'] = 'PropertyEdit';
$components['contentSecurityPolicys']['propertys']['mutation']['fieldsTabs'] = $standardFieldsTabs;
$components['contentSecurityPolicys']['propertys']['mutation']['fields'] = $standardFields;

// ToDo default by select
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['mutationMode'] =
    ['type' => 'select', 'tab' => 'general', 'row' => 'base', 'default' => 'extend', 'showInList' => true];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['mutationMode']['selects'] = [
    'append' => 'append',
    'extend' => 'extend',
    'inherit-again' => 'inherit-again',
    'inherit-once' => 'inherit-once',
    'reduce' => 'reduce',
    'remove' => 'remove',
    'set' => 'set',
];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['directive'] =
     ['type' => 'select', 'tab' => 'general', 'row' => 'base', 'showInList' => true];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['directive']['selects'] = [
    'base-uri' => 'base-uri',
    'child-src' => 'child-src',
    'connect-src' => 'connect-src',
    'default-src' => 'default-src',
//    'Fenced-frame-src' => 'fenced-frame-src', // ???
    'font-src' => 'font-src',
    'form-action' => 'form-action',
    'form-ancestors' => 'frame-ancestors',
    'frame-src' => 'frame-src',
    'img-src' => 'img-src',
    'manifest-src' => 'manifest-src',
    'media-src' => 'media-src',
    'object-src' => 'object-src',
    'plugin-types' => 'plugin-types',
    'report-to' => 'report-to',
    'report-uri' => 'report-uri',
    'requiretrusted-typesfor' => 'require-trusted-types-for',
    'sandbox' => 'sandbox',
    'script-src' => 'script-src',
    'script-src-attr' => 'script-src-attr',
    'script-src-elem' => 'script-src-elem',
    'style-src' => 'style-src',
    'style-src-attr' => 'style-src-attr',
    'style-src-elem' => 'style-src-elem',
    'trusted-types' => 'trusted-types',
    'upgrade-insecure-requests' => 'upgrade-insecure-requests',
    'worker-src' => 'worker-src',
];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['sourceKeyword'] =
    ['type' => 'select', 'tab' => 'general', 'row' => 'base', 'showInList' => true];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['sourceKeyword']['selects'] = [
    0 => 'no',
    'nonce-proxy' => 'nonce-proxy',
    'none' => 'none',
    'report-sample' => 'report-sample',
    'self' => 'self',
    'strict-dynamic' => 'strict-dynamic',
    'unsafe-eval' => 'unsafe-eval',
    'unsafe-hashes' => 'unsafe-hashes',
    'unsafe-inline' => 'unsafe-inline',
    'wasm-unsafe-eval' => 'wasm-unsafe-eval',
];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['sourceScheme'] =
    ['type' => 'select', 'tab' => 'general', 'row' => 'base', 'showInList' => true];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['sourceScheme']['selects'] = [
    0 => 'no',
    'data' => 'data',
    'blob' => 'blob',
    'filesystem' => 'filesystem',
    'http' => 'http',
    'https' => 'https',
    'mediastream' => 'mediastream',
    'ws' => 'ws',
    'wss' => 'wss',
];
$components['contentSecurityPolicys']['propertys']['mutation']['fields']['uriValue'] =
    ['type' => 'input', 'tab' => 'general', 'showInList' => true];
