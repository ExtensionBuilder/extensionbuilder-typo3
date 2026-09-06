<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
class Json
{
    /**
     * @since 0.12
     */
    public static function write(
        string $jsonFile,
        array $arrayForJson,
    ): void {
        $directory = dirname($jsonFile);

        if (!is_dir($directory)) {
            GeneralUtility::mkdir_deep($directory);
        }

        $json = json_encode(
            $arrayForJson,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        file_put_contents($jsonFile, $json, LOCK_EX);
    }

    /**
     * @since 0.12
     */
    public static function read(
        string $jsonFile,
    ): array {
        $error = '';

        // ToDo LLL support
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
            $return = [];

            // ToDo cut path
            //            $jsonFile = substr($jsonFile, strlen(Tools\ExtensionbuilderFolder::getVendorsAndExtensionsBaseFolder()));

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

    /**
     * @since 0.12
     */
    public static function getJsonWithcUrl(
        string $url,
    ): array {
        $return = [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        if ($output) {
            $return = (array)json_decode($output, true);
            if (json_last_error() === 0) {
                $return = array_values($return);
                $return = $return[0];
            }
        }

        return $return;
    }
}