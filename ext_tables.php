<?php

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
    'web_info',
    \T3\FileCanonical\Report\FileCanonicalReport::class,
    '',
    'LLL:EXT:file_canonical/Resources/Private/Language/locallang.xlf:file_canonical.overview'
);
