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

$components['fePlugins'] = [];
$components['fePlugins']['title'] = 'Plugin';
$components['fePlugins']['disable'] = true;
$components['fePlugins']['controller'] = 'Component';
$components['fePlugins']['add'] = 'ComponentAdd';
$components['fePlugins']['edit'] = 'ComponentEdit';
$components['fePlugins']['fieldsTabs'] = $standardFieldsTabs;
$components['fePlugins']['fields'] = $standardFields;
$components['fePlugins']['propertys'] = [];
