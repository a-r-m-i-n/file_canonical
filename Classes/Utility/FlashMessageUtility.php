<?php

declare(strict_types = 1);

namespace T3\FileCanonical\Utility;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlashMessageUtility
{
    public static function addFlashMessage(
        string $messageBody,
        string $messageTitle = '',
        int $severity = AbstractMessage::OK,
        bool $storeInSession = true
    ): void {
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $messageBody,
            $messageTitle,
            $severity,
            $storeInSession
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($message);
    }
}
