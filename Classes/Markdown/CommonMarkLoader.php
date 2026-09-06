<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Markdown;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

final class CommonMarkLoader
{
    private static bool $loaded = false;

    /**
     * @since 0.14
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Composer installation:
        // TYPO3/Composer has already loaded the Composer autoloader.
        if (class_exists(\League\CommonMark\CommonMarkConverter::class)) {
            self::$loaded = true;

            return;
        }

        // Legacy installation:
        // Load the Composer autoloader bundled with the extension.
        $autoloadFile = dirname(__DIR__, 2)
            . '/Contrib/commonmark/vendor/autoload.php';

        if (!is_file($autoloadFile)) {
            throw new \RuntimeException(
                'League CommonMark could not be loaded. '
                . 'Neither the Composer installation nor the bundled '
                . 'Legacy installation was found.',
                1757070001
            );
        }

        require_once $autoloadFile;

        if (!class_exists(\League\CommonMark\CommonMarkConverter::class)) {
            throw new \RuntimeException(
                'League CommonMark was not found after loading the Legacy autoloader.',
                1757070002
            );
        }

        self::$loaded = true;
    }
}