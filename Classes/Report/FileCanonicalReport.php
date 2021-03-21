<?php

declare(strict_types = 1);

namespace T3\FileCanonical\Report;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use T3\FileCanonical\FileCanonicalManager;
use T3\FileCanonical\Utility\FlashMessageUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class FileCanonicalReport
{
    private FileCanonicalManager $manager;

    public function __construct(FileCanonicalManager $manager = null)
    {
        $this->manager = $manager ?? GeneralUtility::makeInstance(FileCanonicalManager::class);
    }

    public function main(): string
    {
        /** @var \TYPO3\CMS\Core\Http\ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];

        if (isset($request->getQueryParams()['clearFileCanonical'])) {
            $uid = (int)$request->getQueryParams()['clearFileCanonical'];
            if ($uid) {
                $this->manager->updateCanonicalLinkParsedInDatabase('', $uid);
                FlashMessageUtility::addFlashMessage('Removed canonical link for sys_file_metadata:' . $uid);
            }
        }

        $view = $this->makeStandaloneView();
        $view->assign('filesMetadataWithCanonicalLink', $this->manager->getFilesMetadataWithSetCanonicalLink());

        return $view->render();
    }

    private function makeStandaloneView(): StandaloneView
    {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:file_canonical/Resources/Private/Template/Report/FileCanonicalReport.html');

        return $view;
    }
}
