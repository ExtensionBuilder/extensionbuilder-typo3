<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */

class Debug
{

    /**
     * @since 0.12
     */
    static function dump(
        array &$fields,
        array &$bodyParams,
    ): void {

DebuggerUtility::var_dump($this, 'Controller');

	}

}