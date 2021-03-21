<?php

declare(strict_types = 1);

namespace T3\FileCanonical;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
class ExtensionConfiguration
{
    private bool $autoCreateFileCanonicalFromReferer;
    private bool $respectRefererQueryString;
    private int $fileStorageUid;

    public function __construct(array $configuration)
    {
        $this->autoCreateFileCanonicalFromReferer = (bool)$configuration['autoCreateFileCanonicalFromReferer'];
        $this->respectRefererQueryString = (bool)$configuration['respectRefererQueryString'];
        $this->fileStorageUid = (int)$configuration['fileStorageUid'];
    }

    public function isAutoCreateFileCanonicalFromRefererEnabled(): bool
    {
        return $this->autoCreateFileCanonicalFromReferer;
    }

    public function isRespectRefererQueryStringEnabled(): bool
    {
        return $this->respectRefererQueryString;
    }

    public function getFileStorageUid(): int
    {
        return $this->fileStorageUid;
    }
}
