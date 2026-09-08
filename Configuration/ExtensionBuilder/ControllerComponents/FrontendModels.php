<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

$components['feModels'] = [];
$components['feModels']['componentName'] = 'frontendmodels';
$components['feModels']['title'] = 'Frontend model (Beta no cosw)';
$components['feModels']['group'] = 'models';
$components['feModels']['disable'] = false;
$components['feModels']['fieldsTabs'] = $standardFieldsTabs;
$components['feModels']['fields'] = $standardFields;

$components['feModels']['fields']['plugin'] = ['type' => 'check', 'default' => true, 'tab' => 'general'];
$components['feModels']['fields']['pluginDescription'] = ['type' => 'input','tab' => 'general'];
$components['feModels']['fields']['pluginGroup'] = ['type' => 'input','tab' => 'general'];

require_once ''
    . $extensionConfigurationPath
    . 'ControllerComponents' . DIRECTORY_SEPARATOR
    . 'Include' . DIRECTORY_SEPARATOR
    . 'Models.php';

$components['feModels']['propertys'] = $components['includeModels']['propertys'];
