<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Core\Environment;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;
use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class RestApiClient
{

    public static function build(
        string $baseUri,
        array $multipart,
    ): array {
        return self::post(
            $baseUri,
            'api/v1/' . Setup\Config::REMOTE_API,
            $multipart,
        );
	}

    public static function getStatus(
        string $baseUri,
    ): array {
        $hostStatus = new Tools\Uri($baseUri);

        if (!$hostStatus->isOnline) { return []; }
		
        $client = new \GuzzleHttp\Client();

        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'getStatus'];

        $response = $client->request(
            'POST',
            $baseUri . 'api/v1/' . Setup\Config::REMOTE_API,
            $multipart,
        );

        $body = (string)$response->getBody() ?? '';

        return (array)json_decode($body, true);
	}

    public static function check(
        string $baseUri,
        array $multipart,
    ): array {
        $client = new \GuzzleHttp\Client();

        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'check'];

        $response = $client->request(
            'POST',
            $baseUri . 'api/v1/' . 'extensionbuilder',
            $multipart,
        );

        $string = (string)$response->getBody();

        return (array)json_decode($string, true);
	}

    public static function register(
        string $baseUri,
        array $multipart,
    ): array {
        $client = new \GuzzleHttp\Client();

        $multipart = [];
        $multipart['multipart'] = [];
        $multipart['multipart'][] = ['name' => 'command', 'contents' => 'register'];

        $response = $client->request(
            'POST',
            $baseUri . 'api/v1/' . 'extensionbuilder',
            $multipart,
        );

        $string = (string)$response->getBody();

        return (array)json_decode($string, true);
	}

    public static function githubSearchRemove(
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
//		debug($result, 'github.com search result');

        if (count($result['items']) > 0) {
            return true;
		} else {
            return false;
		}
	}

    public static function githubRemove(
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

    public static function packagistRemove(
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

    private static function checkUrl(
        string $url,
        int $port = 443,
    ): bool|string {
        $host = Tools\Host::checkUriToHost($url);

// ToDo LLL
        if(!Tools\Host::checkHostDns($host)) { return 'No DNS entry available'; }
        if (!Tools\Host::checkHostPing($host)){ return 'Ping does not respond'; }
        if (!Tools\Host::checkHostPort($host,$port)){ return 'Serviceport is offline'; }

        return true;
	}

    private static function post(
        string $baseUri,
        string $apiPath,
        array $multipart,
    ): array {
        return self::restapiPostGet(
            'POST',
            $baseUri,
            $apiPath,
            $multipart,
        );
	}

    private static function get(
        string $baseUri,
        string $apiPath,
        array $multipar,
    ): array {
        return self::restapiPostGet(
            'GET',
            $baseUri,
            $apiPath,
            $multipar,
        );
	}

    private static function restapiPostGet(
        string $modePostGet,
        string $baseUri,
        string $apiPath,
        array $multipart,
    ): array {
        $client = new \GuzzleHttp\Client();

        $response = $client->request(
            $modePostGet,
            $baseUri . $apiPath,
            $multipart,
        );

        $string = (string)$response->getBody();
		
        file_put_contents(
            Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3temp' . DIRECTORY_SEPARATOR
            . 'response.json',
            $string
        );

//ToDo start postion des JSON daten besser finden 
        $pos = strpos($string, "\"version\"");

        if ($pos > 0) {
            $body = substr($string, $pos - 6);
            $info = substr($string, 0, $pos - 1 - 5);
        } else {
            $body = $string;
            $info = '';
        }

        $return = (array)json_decode($body, true);

        $debugPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\Config::VAR_EB . DIRECTORY_SEPARATOR
            . $return['vendor'] . DIRECTORY_SEPARATOR
            . $return['extension'] . DIRECTORY_SEPARATOR
            . 'debug' . DIRECTORY_SEPARATOR;

        file_put_contents(
            $debugPath . 'response.json',
            $body,
        );

        if ($info) {
           file_put_contents(
               $debugPath . 'info.html',
               $info,
           );
        }

        return $return;
	}

}