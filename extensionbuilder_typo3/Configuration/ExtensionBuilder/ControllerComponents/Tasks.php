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

// @extensionScannerIgnoreLine
$components['tasks'] = [];
// @extensionScannerIgnoreLine
$components['tasks']['componentName'] = 'tasks';
// @extensionScannerIgnoreLine
$components['tasks']['title'] = 'Task';
// @extensionScannerIgnoreLine
$components['tasks']['disable'] = true;
// @extensionScannerIgnoreLine
$components['tasks']['docUrl'] = 
    'https://docs.typo3.org/c/typo3/cms-scheduler/main/en-us/DevelopersGuide/CreatingTasks/Index.html';
// @extensionScannerIgnoreLine
$components['tasks']['controller'] = 'Component';
// @extensionScannerIgnoreLine
$components['tasks']['add'] = 'ComponentAdd';
// @extensionScannerIgnoreLine
$components['tasks']['edit'] = 'ComponentEdit';
// @extensionScannerIgnoreLine
$components['tasks']['fieldsTabs'] = $standardFieldsTabs;
// @extensionScannerIgnoreLine
$components['tasks']['fields'] = $standardFields;
// $components['tasks']['fields'][''] = ['type' => 'input', 'tab' => 'general'];
// @extensionScannerIgnoreLine
$components['tasks']['propertys'] = [];
