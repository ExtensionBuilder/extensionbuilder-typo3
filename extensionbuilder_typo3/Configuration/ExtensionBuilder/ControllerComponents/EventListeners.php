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

$components['eventListeners'] = [];
$components['eventListeners']['componentName'] = 'eventListeners';
$components['eventListeners']['title'] = 'EventListener';
$components['eventListeners']['disable'] = true;
$components['eventListeners']['controller'] = 'Component';
$components['eventListeners']['add'] = 'ComponentAdd';
$components['eventListeners']['edit'] = 'ComponentEdit';
$components['eventListeners']['fieldsTabs'] = $standardFieldsTabs;
$components['eventListeners']['fields'] = $standardFields;
$components['eventListeners']['propertys'] = [];
