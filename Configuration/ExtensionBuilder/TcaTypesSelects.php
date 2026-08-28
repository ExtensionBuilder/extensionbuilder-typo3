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

$tcaTypesSelects = [];

$tcaTypesSelects['category'] = 'category';
$tcaTypesSelects['check'] = 'check';
$tcaTypesSelects['bool'] = 'bool';
$tcaTypesSelects['checkboxToggle'] = 'checkboxToggle';
$tcaTypesSelects['checkboxLabeledToggle'] ='checkboxLabeledToggle';
$tcaTypesSelects['color'] = 'color';
$tcaTypesSelects['datetime'] = 'datetime';
$tcaTypesSelects['date'] = 'date';
$tcaTypesSelects['email'] = 'email';
$tcaTypesSelects['file'] = 'file';
$tcaTypesSelects['uploadToFile'] = 'uploadToFile';
$tcaTypesSelects['flex'] = 'flex';
$tcaTypesSelects['folder'] = 'folder';
$tcaTypesSelects['group'] = 'group';
$tcaTypesSelects['imageManipulation'] = 'imageManipulation';
$tcaTypesSelects['inline'] = 'inline';
$tcaTypesSelects['input'] = 'input';
$tcaTypesSelects['json'] = 'json';
$tcaTypesSelects['language'] = 'language';
$tcaTypesSelects['country'] = 'country';
$tcaTypesSelects['link'] = 'link';
$tcaTypesSelects['none'] = 'none';
$tcaTypesSelects['map'] = 'map';
$tcaTypesSelects['repetition'] = 'repetition';
$tcaTypesSelects['number'] = 'number';
$tcaTypesSelects['decimal'] = 'decimal';
$tcaTypesSelects['passthrough'] = 'passthrough';
$tcaTypesSelects['password'] = 'password';
$tcaTypesSelects['radio'] = 'radio';
$tcaTypesSelects['selectSingle'] = 'selectSingle';
$tcaTypesSelects['selectSingleBox'] = 'selectSingleBox';
$tcaTypesSelects['selectCheckBox'] = 'selectCheckBox';
$tcaTypesSelects['selectMultipleSideBySide'] = 'selectMultipleSideBySide';
$tcaTypesSelects['selectTree'] = 'selectTree';
$tcaTypesSelects['slug'] = 'slug';
$tcaTypesSelects['text'] = 'text';
$tcaTypesSelects['textrte'] = 'textrte';
$tcaTypesSelects['textt3editor'] = 'textt3editor';
$tcaTypesSelects['textbelayoutwizard'] = 'textbelayoutwizard';
$tcaTypesSelects['texttable'] = 'texttable';
$tcaTypesSelects['user'] = 'user';
$tcaTypesSelects['uuid'] = 'uuid';

return $tcaTypesSelects;