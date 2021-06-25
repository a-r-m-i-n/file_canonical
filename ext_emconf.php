<?php

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Canonical Links for files',
    'description' => 'Provides canonical links for sys_files in TYPO3 CMS.',
    'category' => 'Frontend',
    'shy' => 0,
    'version' => '1.0.0',
    'state' => 'stable',
    'author' => 'Armin Vieweg',
    'author_email' => 'info@v.ieweg.de',
    'author_company' => 'v.ieweg Webentwicklung',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.0.99',
            'typo3' => '10.4.0-11.99.99',
        ],
    ],
    'autoload' => [
        'psr-4' => ['T3\\FileCanonical\\' => 'Classes'],
    ],
];
// @codingStandardsIgnoreEnd
