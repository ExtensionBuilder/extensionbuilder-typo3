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
        $tmpError = '';

        if (file_exists($jsonFile)) {
            $tmpArray = json_decode(file_get_contents($jsonFile), true);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
		    		$tmpError = 'Maximale Stacktiefe überschritten';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $tmpError = 'Unterlauf oder Nichtübereinstimmung der Modi';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $tmpError = 'Unerwartetes Steuerzeichen gefunden';
                    break;
                case JSON_ERROR_SYNTAX:
                    $tmpError = 'Syntaxfehler';
                    break;
                case JSON_ERROR_UTF8:
                    $tmpError = 'Missgestaltete UTF-8 Zeichen, möglicherweise fehlerhaft kodiert';
                    break;
                default:
                    $tmpError = 'Unbekannter Fehler';
                    break;
            }
		} else {
            $tmpError = 'File not found';
		}

        if ($tmpError) {
            $tmpArray = [];

            $jsonFile = substr($jsonFile, strlen(Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder()));

            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $jsonFile,
                'Tools\Json::read: ' . $tmpError,
                ContextualFeedbackSeverity::ERROR,
            );
            $notificationQueue->enqueue($flashMessage);
		}

        return $tmpArray;
    }

}