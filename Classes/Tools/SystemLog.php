<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */
class SystemLog
{
    /**
     * @since 0.14
     *
     * @param array<string, mixed> $configArray
     */
    public static function writeSystemLog(
        array &$configArray,
        string $search,
        string $replace,
    ): void {

        $GLOBALS['BE_USER']->writelog(
            4, // type: Extensions / Module
            0, // action: keine Unterkategorie
            0, // error: 0=Message, 1=Error, 2=System Error, 3=Security
            null, // unbenutzt
            'Meine Extension: Datensatz %s wurde verarbeitet - ',
            [1], //[$uid],
            'tx_myext_domain_model_foo', // Tabellenname
            1, // $uid, // Record UID
            null, // unbenutzt
            1, //$pid // event_pid
        );

    }
}