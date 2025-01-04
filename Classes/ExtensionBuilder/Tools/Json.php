<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

use ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class Json
{

    static function write(
        string $jsonFile,
        array $arrayForJson,
    ): void {
        file_put_contents(
            $jsonFile,
            json_encode(
                $arrayForJson,
                JSON_PRETTY_PRINT,
            )
        );
    }

    static function read(
        string $jsonFile,
    ): array {
        $error = '';

// ToDO LLL

        if (file_exists($jsonFile)) {
            $return = json_decode(file_get_contents($jsonFile), true);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
		    		$error = 'Maximale Stacktiefe überschritten';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Unterlauf oder Nichtübereinstimmung der Modi';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unerwartetes Steuerzeichen gefunden';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntaxfehler';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Missgestaltete UTF-8 Zeichen, möglicherweise fehlerhaft kodiert';
                    break;
                default:
                    $error = 'Unbekannter Fehler';
                    break;
            }
		} else {
            $error = 'File not found';
		}

        if ($error) {
// ToDo
//debug(debug_backtrace()[0],'0');
//debug(debug_backtrace()[2],'2');

            $return = [];

            $jsonFile = substr($jsonFile, strlen(Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder()));

            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $jsonFile,
                'Tools\Json::read: ' . $error,
                ContextualFeedbackSeverity::ERROR,
            );
            $notificationQueue->enqueue($flashMessage);
		}

        return $return;
    }

}