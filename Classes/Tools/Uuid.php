<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */
class Uuid
{
    /**
     * @since 0.12
     */
    public static function uuid(
        ?string $data = null,
    ): string {
        $data ??= random_bytes(16);

        if (strlen($data) !== 16) {
            throw new \InvalidArgumentException('UUID data must be exactly 16 bytes.');
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @since 0.12
     */
    public static function createSystemId(
        string $uuid = '',
    ): string {
        return hash(
            'sha256',
            json_encode(
                [
                    'host' => (string)($_SERVER['HTTP_HOST'] ?? ''),
                    'server_name' => (string)($_SERVER['SERVER_NAME'] ?? ''),
                    'document_root' => (string)($_SERVER['DOCUMENT_ROOT'] ?? ''),
                    'php_uname' => php_uname('n'),
                    'uuid' => ($uuid),
                ],
                JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            )
        );
    }

}
