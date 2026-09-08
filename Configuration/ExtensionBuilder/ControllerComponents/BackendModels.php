<?php

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

$components['beModels'] = [];
$components['beModels']['componentName'] = 'backendmodels';
$components['beModels']['title'] = 'Backend model (Beta no cosw)';
$components['beModels']['group'] = 'models';
$components['beModels']['disable'] = false;

//$components['beModels']['fieldsTabs'] = $standardFieldsTabs;
$components['beModels']['fields'] = $standardFields;

$components['beModels']['fields']['plugin'] = ['type' => 'check', 'default' => true, 'tab' => 'general'];
$components['beModels']['fields']['pluginDescription'] = ['type' => 'input','tab' => 'general'];
$components['beModels']['fields']['pluginGroup'] = ['type' => 'input','tab' => 'general'];

require_once ''
    . $extensionConfigurationPath
    . 'ControllerComponents' . DIRECTORY_SEPARATOR
    . 'Include' . DIRECTORY_SEPARATOR
    . 'Models.php';

$components['beModels']['propertys'] = $components['includeModels']['propertys'];
