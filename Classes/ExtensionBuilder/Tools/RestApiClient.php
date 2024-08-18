<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Core\Environment;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

use ExtensionBuilder\ExtensionbuilderTypo3\Setup;

class RestApiClient
{

    public static function build(
        string $baseUri,
        array $multipart,
    ): array {
        return self::post(
            $baseUri,
            'api/v1/' . Setup\GlobalConfig::REMOTE_API,
            $multipart,
        );
	}

    public static function githubSearch(
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
		debug($result, 'github.com search result');

        if (count($result['items']) > 0) {
            return true;
		} else {
            return false;
		}
	}

    public static function github(
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

    public static function packagist(
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


    // private static function

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
            $baseUri.$apiPath,
            $multipart,
        );

        $tmpSting = (string)$response->getBody();
		
        file_put_contents(
            Environment::getProjectPath() . DIRECTORY_SEPARATOR
            . 'typo3temp' . DIRECTORY_SEPARATOR
            . 'response.json',
            $tmpSting
        );

//ToDo start postion des JSON daten besser finden 
        $tmpPos = strpos($tmpSting, "\"version\"");

        if ($tmpPos > 0) {
            $tmpBody = substr($tmpSting, $tmpPos - 6);
            $tmpInfo = substr($tmpSting, 0, $tmpPos - 1 - 5);
        } else {
            $tmpBody = $tmpSting;
            $tmpInfo = '';
        }

        $tmpReturn = (array)json_decode($tmpBody, true);

        $debugPath =
            Environment::getVarPath() . DIRECTORY_SEPARATOR
            . Setup\GlobalConfig::VAR_EB . DIRECTORY_SEPARATOR
            . $tmpReturn['vendor'] . DIRECTORY_SEPARATOR
            . $tmpReturn['extension'] . DIRECTORY_SEPARATOR
            . 'debug' . DIRECTORY_SEPARATOR;

        file_put_contents(
            $debugPath . 'response.json',
            $tmpBody,
        );

        if ($tmpInfo) {
           file_put_contents(
               $debugPath . 'info.html',
               $tmpInfo,
           );
        }

        return $tmpReturn;
	}

}