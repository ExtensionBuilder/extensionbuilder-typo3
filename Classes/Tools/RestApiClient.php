<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Core\Environment;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class RestApiClient
{

    public static function build(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        return self::executeClientJsonResponseB($authority, $path, $multipart);
	}

    public static function getStatus(
        string $authority,
        string $path,
    ): array {
        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'getStatus'];
        return self::executeClientJsonResponse($authority, $path, $multipart);
	}

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
                $client = new \GuzzleHttp\Client();

                $response = $client->request(
                    'POST',
                    $authority . $path,
                    $multipart,
                );

                $body = (string)$response->getBody() ?? '';

            } catch (RequestException $e) {
// ToDo Error code
                if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                    $body = '{ "status": "error", "statusCode": "' . $e->getResponse()->getStatusCode() . '" }';
                } else {
//                    $body = '{ "status": "Service offline2 - ' . $e->getMessage() . '", "serverUrl": "' . $hostStatus->getHost() . '" }';
                    $body = '{ "status": "error", "statusCode": "' . $e->getResponse()->getStatusCode() . '" }';
                }
            }
		}

        return (array)json_decode($body, true);
	}

    public static function executeClientJsonResponseB(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        $hostStatus = new Tools\Uri($authority);

        if (!$hostStatus->isOnline()) {
            $body = '{ "status": "Server offline" }';
        } else {
            try {
                $client = new \GuzzleHttp\Client();

                $response = $client->request(
                    'POST',
                    $authority . $path,
                    $multipart,
                );

                $body = (string)$response->getBody() ?? '';

                $jsonStart = strpos($body,  '"status":');
                if ($jsonStart > 0) {
                    $body = substr($body, $jsonStart - 6 );
                }

            } catch (RequestException $e) {

                if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                    $body = '{ "status": "error", "statusCode": "' . $e->getResponse()->getStatusCode() . '" }';
                } else {
//                    $body = '{ "status": "Service offline2 - ' . $e->getMessage() . '", "serverUrl": "' . $hostStatus->getHost() . '" }';
                    $body = '{ "status": "error", "statusCode": "' . $e->getResponse()->getStatusCode() . '" }';
                }
            }
		}

        return (array)json_decode($body, true);
	}

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

    public static function githubSearchToRemove(
        array &$extension,
    ): bool {
        $token = $extension['extensionBuild']['gitubCom']['token'] ?? '';
        $vendorName = $extension['extensionBuild']['gitubCom']['vendor'] ?? '';
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
		} else {
            return false;
		}
	}

    public static function githubToRemove(
        array &$extension,
    ): void {
        if (!($extension['extensionBuild']['gitubCom'] ?? false)) { return; }

        $token = $extension['extensionBuild']['gitubCom']['token'] ?? '';
        $vendorName = $extension['extensionBuild']['gitubCom']['vendor'] ?? '';
        $extensionName = $extension['extension']['extensionName'] ?? '';
        $private = false;

        if (!self::githubSearch($extension)) {
            $multipart = [
                'headers' => [
                    'Authorization' => 'token '.$token,
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
//    		debug ($result, 'github.com result');

		}

//        self::packagistOrgUpdate('typo3', 'cms-scheduler');

	}

    public static function packagistToRemove(
        array &$extension,
    ): void {
        // https://packagist.org/apidoc

        if (!($extension['extensionBuild']['packagistOrg'] ?? false)) { return; }

        $token = $extension['extensionBuild']['packagistOrg']['token'] ?? '';
        $username = $extension['extensionBuild']['packagistOrg']['username'] ?? '';
        $vendorName = $extension['extensionBuild']['packagistOrg']['vendor'] ?? '';
        $extensionName = $extension['extension']['extensionName'] ?? '';

        if (self::githubSearch($extension)) {
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