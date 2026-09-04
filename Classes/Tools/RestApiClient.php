<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;

use GuzzleHttp\Exception\RequestException;
use TYPO3\CMS\Core\Log\LogManager;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class RestApiClient
{
    // $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    // $logger->info('Method not allowed');

    /**
     * @since 0.12
     */
    public static function getStatus(
        string $authority,
        string $path,
    ): array {
        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'getStatus'];

        return self::executeClientJsonResponse($authority, $path, $multipart);
    }

    /**
     * @since 0.12
     */
    public static function build(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        // 9999
        return self::executeClientJsonResponse($authority, $path, $multipart);
    }

    /**
     * @since 0.12
     */
    public static function checkKey(
        string $authority,
        string $path,
        string $systemId,
        string $systemProKey,
        string $developerId,
        string $developerProKey,
    ): array {
        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'checkProKey'];
        $multipart['multipart'][] = ['name' => 'systemId', 'contents' => $systemId];
        $multipart['multipart'][] = ['name' => 'systemProKey', 'contents' => $systemProKey];
        $multipart['multipart'][] = ['name' => 'developerId', 'contents' => $developerId];
        $multipart['multipart'][] = ['name' => 'developerProKey', 'contents' => $developerProKey];
        return self::executeClientJsonResponse($authority, $path, $multipart);
    }

    /**
     * @since 0.12
     */
    public static function checkToRemove(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        $client = new \GuzzleHttp\Client();

        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'check'];

        $response = $client->request(
            'POST',
            $authority . $path,
            $multipart,
        );

        $string = (string)$response->getBody();

        return (array)json_decode($string, true);
    }

    /**
     * @since 0.12
     */
    public static function executeClientJsonResponse(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        $hostStatus = new Tools\Uri($authority);

        if (!$hostStatus->isOnline()) {
            $body = '{ "status": "Server offline" }';
        } else {
            try {
                $client = new \GuzzleHttp\Client([
                    'timeout' => 5,
                    'connect_timeout' => 3,
                ]);

                $response = $client->request(
                    'POST',
                    $authority . $path,
                    $multipart,
                );

                $body = (string)$response->getBody() ?? '';
            } catch (RequestException $e) {
                $statusCode = $e->hasResponse()
                    ? $e->getResponse()->getStatusCode()
                    : 0;

                $body = json_encode([
                    'status' => 'Error: ' . $statusCode,
                    'statusCode' => $statusCode,
                    'message' => $e->getMessage(),
                ], JSON_THROW_ON_ERROR);
            }
        }

        $jsonObj = json_decode($body, true);

        if (!(json_last_error_msg() == 'No error')) {

            if ($pos = strpos($body, '"status":') ?? false) {
                $body = "{\n    " . substr($body, $pos);
                $jsonObj = json_decode($body, true);
            }
        }

        if (!(json_last_error_msg() == 'No error')) {
            $jsonObj = json_decode('{ "status": "error", "statusCode": "JSON: ' . json_last_error_msg() . '" }', true);
        }

        return $jsonObj;
    }

    /**
     * @since 0.12
     */
    public static function registerToRemove(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        $client = new \GuzzleHttp\Client();

        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'register'];

        $response = $client->request(
            'POST',
            $authority . $path,
            $multipart,
        );

        $string = (string)$response->getBody();

        return (array)json_decode($string, true);
    }

    /**
     * @since 0.12
     */
    public static function gitHubSearchToRemove(
        array &$extension,
    ): bool {
        $token = $extension['extensionBuild']['gitHubCom']['token'] ?? '';
        $vendorName = $extension['extensionBuild']['gitHubCom']['vendor'] ?? '';
        $extensionName = $extension['extension']['extensionName'] ?? '';

        $multipart = [
            'headers' => [
                'Authorization' => 'token ' . $token,
                'Accept' => 'application/vnd.github+json',
            ],
            'query' => [
                'q' => $vendorName . '/' . $extensionName,
            ],
        ];
        //        $result = self::get('https://api.github.com/', 'search/repositories', $multipart);

        if (count($result['items']) > 0) {
            return true;
        }
        return false;

    }

    /**
     * @since 0.12
     */
    public static function gitHubToRemove(
        array &$extension,
    ): void {
        if (!($extension['extensionBuild']['gitHubCom'] ?? false)) {
            return;
        }

        $token = $extension['extensionBuild']['gitHubCom']['token'] ?? '';
        $vendorName = $extension['extensionBuild']['gitHubCom']['vendor'] ?? '';
        $extensionName = $extension['extension']['extensionName'] ?? '';
        $private = false;

        if (!self::gitHubSearch($extension)) {
            $multipart = [
                'headers' => [
                    'Authorization' => 'token ' . $token,
                    'Accept' => 'application/vnd.github+json',
                ],
                'json' => [
                    'name' => $extensionName,
                    'auto_init' => true,
                    'private' => $private,
                    'gitignore_template' => 'nanoc',
                ],
            ];
            $result = self::post('https://api.github.com/', 'user/repos', $multipart);

        }

        //        self::packagistOrgUpdate('typo3', 'cms-scheduler');

    }

    /**
     * @since 0.12
     */
    public static function packagistToRemove(
        array &$extension,
    ): void {
        // https://packagist.org/apidoc

        if (!($extension['extensionBuild']['packagistOrg'] ?? false)) {
            return;
        }

        $token = $extension['extensionBuild']['packagistOrg']['token'] ?? '';
        $username = $extension['extensionBuild']['packagistOrg']['username'] ?? '';
        $vendorName = $extension['extensionBuild']['packagistOrg']['vendor'] ?? '';
        $extensionName = $extension['extension']['extensionName'] ?? '';

        if (self::gitHubSearch($extension)) {
            $multipart = [
                'query' => [
                    'q' => $vendorName . '/' . $extensionName,
                ],
            ];
            $result = self::get('https://packagist.org/', 'search.json', $multipart);

            if (count($result['results']) === 0) {
                $multipart = [
                    'query' => [
                        'username' => $username,
                        'apiToken' => $token,
                    ],
                    'json' => [
                        'repository' => [
                            'url' => 'https://github.com/extension-builder-com/eb_contacts',
                        ],
                    ],
                ];
                $result = self::post('https://packagist.org/', 'api/create-package', $multipart);
            }
        }
    }

}
