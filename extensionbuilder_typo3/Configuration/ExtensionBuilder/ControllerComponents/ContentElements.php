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

$components['contentElements'] = [];
$components['contentElements']['componentName'] = 'contentElements';
$components['contentElements']['title'] = 'ContentElement';
$components['contentElements']['disable'] = true;
$components['contentElements']['controller'] = 'Component';
$components['contentElements']['add'] = 'ComponentAdd';
$components['contentElements']['edit'] = 'ComponentEdit';
$components['contentElements']['fieldsTabs'] = $standardFieldsTabs;
$components['contentElements']['fields'] = $standardFields;
$components['contentElements']['propertys'] = [];
