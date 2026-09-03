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

$components['extConfTemplates'] = [];
$components['extConfTemplates']['componentName'] = 'extConfTemplates';
$components['extConfTemplates']['title'] = 'extConfTemplates';
$components['extConfTemplates']['disable'] = true;
$components['extConfTemplates']['docUrl'] = 
    'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/FileStructure/ExtConfTemplate.html';
$components['extConfTemplates']['controller'] = 'Component';
$components['extConfTemplates']['add'] = 'ComponentAdd';
$components['extConfTemplates']['edit'] = 'ComponentEdit';
$components['extConfTemplates']['fieldsTabs'] = $standardFieldsTabs;
$components['extConfTemplates']['fields'] = $standardFields;

$components['extConfTemplates']['fields']['nested'] = ['type' => 'input', 'tab' => 'general'];
$components['extConfTemplates']['fields']['type'] = ['type' => 'select', 'tab' => 'general'];
$components['extConfTemplates']['fields']['type']['selects'] = [
        'boolean' => 'boolean',
        'color' => 'color',
        'int' => 'int',
        'int+' => 'int+',
        'integer' => 'integer',
        'offset' => 'offset',
        'options' => 'options',
        'small' => 'small',
        'string' => 'string',
        'user' => 'user',
        'wrap' => 'wrap',
    ];
// cat
// label
// loginLogo
