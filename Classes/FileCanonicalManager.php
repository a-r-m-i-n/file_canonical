<?php

declare(strict_types = 1);

namespace T3\FileCanonical;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\MimeTypes;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as ExtensionConfigurationService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FileCanonicalManager implements SingletonInterface
{
    private static ?ExtensionConfiguration $config = null;

    /**
     * @return FileInterface|File|null
     */
    public function getFileFromUri(string $uri): ?FileInterface
    {
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $storage = $resourceFactory->getStorageObject($this->getConfig()->getFileStorageUid());
        } catch (\InvalidArgumentException $e) {
            $storage = $resourceFactory->getDefaultStorage();
        }
        $storagePublicUrl = $storage->getRootLevelFolder()->getPublicUrl();
        $currentFileIdentifier = substr($uri, strlen($storagePublicUrl));
        try {
            /* @var FileInterface|File $file */
            return $storage->getFile($currentFileIdentifier);
        } catch (\InvalidArgumentException $e) {
        }

        return null;
    }

    public function buildResponseForFile(string $filePath): Response
    {
        $mimeType = new MimeTypes();
        $mimeType = $mimeType->guessMimeType($filePath);

        $response = new Response();

        return $response
            ->withHeader('Content-Length', (string)filesize($filePath))
            ->withHeader('Content-Type', $mimeType)
            ->withBody(new Stream($filePath))
        ;
    }

    public function getConfig(): ExtensionConfiguration
    {
        if (self::$config) {
            return self::$config;
        }
        /** @var ExtensionConfigurationService $extensionConfigService */
        $extensionConfigService = GeneralUtility::makeInstance(ExtensionConfigurationService::class);
        $config = $extensionConfigService->get('file_canonical');

        return self::$config = GeneralUtility::makeInstance(ExtensionConfiguration::class, $config ?? []);
    }

    public function checkCanonicalUrlFromRefererAndUpdateMetadata(File $file, ServerRequestInterface $request): void
    {
        $refererHeader = $request->getHeader('referer');
        $refererUrl = reset($refererHeader);
        $refererUrlParts = parse_url($refererUrl);
        // Check that referer host name and current host name is the same
        if ($refererUrlParts['host'] === $request->getUri()->getHost()) {
            $canonicalLink = $refererUrl;
            if (!$this->getConfig()->isRespectRefererQueryStringEnabled()) {
                $canonicalLink = $refererUrlParts['scheme'] . '://' . $refererUrlParts['host'] . $refererUrlParts['path'];
            }
            $this->updateCanonicalLinkParsedInDatabase($canonicalLink, $file->getMetaData()->get()['uid']);
        }
    }

    public function updateCanonicalLinkParsedInDatabase(string $canonicalLinkParsed, int $sysFileMetadataUid): int
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $pool->getConnectionForTable('sys_file_metadata');

        return $connection->update('sys_file_metadata', [
            'canonical_link_parsed' => $canonicalLinkParsed,
            'tstamp' => time(),
        ], ['uid' => $sysFileMetadataUid]);
    }
}
