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





    public static function checkRemove(
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

    public static function registerRemove(
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

    private static function checkUrlRemove(
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

    private static function postRemove(
        string $authority,
        string $path,
        array $multipart,
    ): array {
        return self::restapiPostGet(
            'POST',
            $authority,
            $path,
            $multipart,
        );
	}

    private static function getRemove(
        string $authority,
        string $path,
        array $multipar,
    ): array {
        return self::restapiPostGet(
            'GET',
            $authority,
            $path,
            $multipar,
        );
	}

    private static function restapiPostGetRemove(
        string $modePostGet,
        string $authority,
        string $path,
        array $multipart,
    ): array {
        $client = new \GuzzleHttp\Client();

try {
        $response = $client->request(
            $modePostGet,
            $authority . $path,
            $multipart,
        );
} catch (RequestException $e) {
    if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
        // Hier fängst du den 404 gezielt ab
        echo '404 - Seite nicht gefunden';
    } else {
        // Andere Fehler kannst du hier behandeln oder weiterwerfen
        echo 'Ein anderer Fehler ist aufgetreten: ' . $e->getMessage();
    }
}
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

// ToDo
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

//debug((array)json_decode($body, true),'RestApiClient.php');

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

//debug($body,'body2');
//$txt = "<html><body>\n" . $body ."\n</body></html>";
//file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log.html',$txt);
//file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log.txt',$body);

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

}