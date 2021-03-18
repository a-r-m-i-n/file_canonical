<?php

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
$additionalColumns = [
    'canonical_link' => [
        'exclude' => false,
        'label' => 'LLL:EXT:file_canonical/Resources/Private/Language/locallang.xlf:canonical_link',
        'description' => 'LLL:EXT:file_canonical/Resources/Private/Language/locallang.xlf:canonical_link.description',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
            'size' => 30,
            'eval' => 'trim',
            'softref' => 'typolink,typolink_tag,images,url',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => 'file,folder,mail,spec,,telephone',
                        'blindLinkFields' => 'class,params,target,title',
                    ],
                ],
            ],
        ]
    ],
    'canonical_link_parsed' => [
        'exclude' => false,
        'label' => 'LLL:EXT:file_canonical/Resources/Private/Language/locallang.xlf:canonical_link_parsed',
        'description' => 'LLL:EXT:file_canonical/Resources/Private/Language/locallang.xlf:canonical_link_parsed.description',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'readOnly' => true,
        ],
    ]
];

$GLOBALS['TCA']['sys_file_metadata']['palettes']['canonical_link']['showitem'] = 'canonical_link, canonical_link_parsed';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'canonical_link');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'canonical_link_parsed');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', '--palette--;;canonical_link', '', 'after:fileinfo');
