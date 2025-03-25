<?php

// Datei: Configuration/TCA/Overrides/tt_content.php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'my_custom_field' => [
        'exclude' => 1,
        'label' => 'My Custom Field',
        'config' => [
            'type' => 'text',
            'enableRichtext' => true, // Aktiviert den CKEditor
            'softref' => 'typolink_tag, images, email',
            'default' => 'test CK', // Standardwert, wenn der Text leer ist
        ]
    ],
]);

// Das benutzerdefinierte Feld der Tabelle hinzufügen
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'my_custom_field');
