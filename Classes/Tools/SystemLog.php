<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.14
 */

class SystemLog
{

    /**
     * @since 0.14
     */
    static function writeSystemLog(
        array &$tmpConfigArray,
        string $search,
        string $replace,
    ): void {

        $GLOBALS['BE_USER']->writelog(
            4, // type: Extensions / Module
            0, // action: keine Unterkategorie
            0, // error: 0=Message, 1=Error, 2=System Error, 3=Security
            null, // unbenutzt
            'Meine Extension: Datensatz %s wurde verarbeitet - '. $vendorName,
            [1], //[$uid],
            'tx_myext_domain_model_foo', // Tabellenname
            1, // $uid, // Record UID
            null, // unbenutzt
            1, //$pid // event_pid
        );

	}

}