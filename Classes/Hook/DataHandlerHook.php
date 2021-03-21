<?php

declare(strict_types = 1);

namespace T3\FileCanonical\Hook;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use T3\FileCanonical\FileCanonicalManager;
use T3\FileCanonical\Utility\TsfeBackendUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class DataHandlerHook
{
    /**
     * @param string|int $id
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $pObj
    ): void {
        if ('sys_file_metadata' === $table && in_array($status, ['new', 'update'])) {
            $uid = $this->getUid($id, $table, $status, $pObj);

            if (array_key_exists('canonical_link', $fieldArray)) {
                $row = BackendUtility::getRecord('sys_file_metadata', $uid);

                /** @var LinkService $linkService */
                $linkService = GeneralUtility::makeInstance(LinkService::class);
                try {
                    $params = $linkService->resolve($row['canonical_link']);
                } catch (\Exception $e) {
                }

                $pageUid = 0;
                if (isset($params['type'], $params['pageuid']) && 'page' === $params['type']) {
                    $pageUid = (int)$params['pageuid'];
                }

                TsfeBackendUtility::initializeTypoScriptFrontendController($pageUid);

                /** @var ContentObjectRenderer $contentObject */
                $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                $url = empty($row['canonical_link']) ? '' : $contentObject->typoLink_URL([
                    'parameter' => $row['canonical_link'],
                    'forceAbsoluteUrl' => true,
                    'linkAccessRestrictedPages' => true,
                ]);

                /** @var FileCanonicalManager $manager */
                $manager = GeneralUtility::makeInstance(FileCanonicalManager::class);
                $ret = $manager->updateCanonicalLinkParsedInDatabase($url, $uid);
                if ($ret) {
                    if (empty($url)) {
                        $this->addFlashMessage('', 'Canonical Link has been reset'); // TODO: Translate
                    } else {
                        $this->addFlashMessage($url, 'Canonical Link has been parsed and updated.'); // TODO: Translate
                    }

                    return;
                }
                $this->addFlashMessage(sprintf('Unable to parse and update canonical Link "%s". Following source given: %s', $url, $row['canonical_link']), 'Error', AbstractMessage::ERROR); // TODO: Translate
            }
        }
    }

    /**
     * @param string|int $id
     */
    private function getUid($id, string $table, string $status, DataHandler $pObj): int
    {
        $uid = $id;
        if ('new' === $status) {
            if (!$pObj->substNEWwithIDs[$id]) {
                //postProcessFieldArray
                $uid = 0;
            } else {
                //afterDatabaseOperations
                $uid = $pObj->substNEWwithIDs[$id];
                if (isset($pObj->autoVersionIdMap[$table][$uid])) {
                    $uid = $pObj->autoVersionIdMap[$table][$uid];
                }
            }
        }

        return (int)$uid;
    }

    /**
     * @param string $messageBody
     * @param string $messageTitle
     * @param int    $severity
     * @param bool   $storeInSession
     */
    private function addFlashMessage($messageBody, $messageTitle = '', $severity = AbstractMessage::OK, $storeInSession = true): void
    {
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
