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

$components['itemGroups'] = [];
$components['itemGroups']['componentName'] = 'itemGroups';
$components['itemGroups']['title'] = 'Item Groups';
$components['itemGroups']['disable'] = false;
$components['itemGroups']['controller'] = 'Component';
$components['itemGroups']['add'] = 'ComponentAdd';
$components['itemGroups']['edit'] = 'ComponentEdit';
$components['itemGroups']['fieldsTabs'] = $standardFieldsTabs;
$components['itemGroups']['fields'] = $standardFields;

	   