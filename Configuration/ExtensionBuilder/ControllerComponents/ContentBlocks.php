<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.13
 */

$components['contentBlocks'] = [];
$components['contentBlocks']['componentName'] = 'contentBlocks';
$components['contentBlocks']['docUrl'] = 'https://docs.typo3.org/p/friendsoftypo3/content-blocks/main/en-us/';
$components['contentBlocks']['title'] = 'Content Blocks';
$components['contentBlocks']['disable'] = true;
$components['contentBlocks']['fieldsTabs'] = $standardFieldsTabs;
$components['contentBlocks']['fields'] = $standardFields;

$components['contentBlocks']['fields']['contentType'] = ['type' => 'select', 'tab' => 'general', 'required' => true];
$components['contentBlocks']['fields']['contentType']['selects'] = [
    'content-element' => 'Content Element',
    'page-type' => 'Page Type',
    'file-type' => 'File Type',
    'record-type' => 'Record Type',
];

$components['contentBlocks']['propertys'] = [];
$components['contentBlocks']['propertys']['fields'] = [];
$components['contentBlocks']['propertys']['fields']['propertysName'] = 'fields';
$components['contentBlocks']['propertys']['fields']['title'] = 'Fields';
$components['contentBlocks']['propertys']['fields']['disable'] = false;
$components['contentBlocks']['propertys']['fields']['fieldsTabs'] = $standardFieldsTabs;
$components['contentBlocks']['propertys']['fields']['fieldsTabs']['tab'] = [];
$components['contentBlocks']['propertys']['fields']['fields'] = $standardFields;
$components['contentBlocks']['propertys']['fields']['fields']['type'] = ['type' => 'select', 'tab' => 'general'];
$components['contentBlocks']['propertys']['fields']['fields']['type']['selects'] = &$this->tcaTypesSelects;
$components['contentBlocks']['propertys']['fields']['fields']['size'] = ['type' => 'input', 'tab' => 'general'];
$components['contentBlocks']['propertys']['fields']['fields']['readOnly'] = ['type' => 'check', 'tab' => 'general'];
$components['contentBlocks']['propertys']['fields']['fields']['label'] = ['type' => 'check', 'tab' => 'general'];
$components['contentBlocks']['propertys']['fields']['fields']['backendLabe'] = ['type' => 'check', 'tab' => 'general'];
