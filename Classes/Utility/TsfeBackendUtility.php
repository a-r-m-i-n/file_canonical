<?php

declare(strict_types = 1);

namespace T3\FileCanonical\Utility;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TsfeBackendUtility implements SingletonInterface
{
    /** @var int|null */
    private static $pageId;

    public static function initializeTypoScriptFrontendController(int $pageId, int $pageType = 0, int $sysLanguageUid = 0): void
    {
        if (null !== self::$pageId) {
            if ($pageId !== self::$pageId) {
                throw new \RuntimeException('TSFE was already initialized with different root page ID', 1613146956);
            }
            // already initialized => nothing to do
            return;
        }

        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $pageId ? $siteFinder->getSiteByPageId($pageId) : array_values($siteFinder->getAllSites())[0];

        $_SERVER['HTTP_HOST'] = $site->getBase()->getHost();
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        /** @var TypoScriptFrontendController $tsfe */
        $tsfe = GeneralUtility::makeInstance(TypoScriptFrontendController::class, null, $site, $site->getLanguageById($sysLanguageUid));
        $tsfe->newCObj();
        $GLOBALS['TSFE'] = $tsfe;

        $extbaseConfigurationManager = GeneralUtility::makeInstance(ObjectManager::class)->get(ConfigurationManagerInterface::class);
        $extbaseConfigurationManager->setContentObject($tsfe->cObj);

        /** @var TemplateService $template */
        $template = GeneralUtility::makeInstance(TemplateService::class);
        $template->tt_track = false;
        $template->setProcessExtensionStatics(true);
        $rootline = [];
        if ($pageId > 0) {
            try {
                $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageId)->get();
            } catch (\RuntimeException $e) {
                $rootline = [];
            }
        }
        $template->runThroughTemplates($rootline);
        $template->generateConfig();

        $tsfe->fe_user = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $tsfe->fe_user->start();
        $tsfe->fe_user->unpack_uc();

        $tsfe->tmpl = $template;
        $tsfe->fetch_the_id();

        self::$pageId = $pageId;
    }
}
