<?php

declare(strict_types = 1);

namespace T3\FileCanonical;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */

use Doctrine\DBAL\Statement;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mime\MimeTypes;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as ExtensionConfigurationService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

final class FileCanonicalManager implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
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
            if ($storage->hasFile($currentFileIdentifier)) {
                /* @var FileInterface|File $file */
                return $storage->getFile($currentFileIdentifier);
            }
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

        try {
            /** @var ExtensionConfigurationService $extensionConfigService */
            $extensionConfigService = GeneralUtility::makeInstance(ExtensionConfigurationService::class);
            $config = $extensionConfigService->get('file_canonical');
        } catch (\Exception $e) {
        }

        return self::$config = GeneralUtility::makeInstance(ExtensionConfiguration::class, $config ?? []);
    }

    /**
     * @return string|null The checked (and sanitized) canonical URL
     */
    public function checkCanonicalUrlFromRefererAndUpdateMetadata(File $file, ServerRequestInterface $request): ?string
    {
        $refererHeader = $request->getHeader('referer');
        $refererUrl = reset($refererHeader);
        $refererUrlParts = parse_url($refererUrl);
        // Check that referer host name and current host name is the same
        // And hat REQUEST_URI does not start with /typo3/
        if ($refererUrlParts['host'] === $request->getUri()->getHost() &&
            !StringUtility::beginsWith($refererUrlParts['path'], '/typo3/')
        ) {
            $canonicalLink = $refererUrl;
            if (!$this->getConfig()->isRespectRefererQueryStringEnabled()) {
                $canonicalLink = $refererUrlParts['scheme'] . '://' . $refererUrlParts['host'] . $refererUrlParts['path'];
            }
            $this->updateCanonicalLinkParsedInDatabase($canonicalLink, $file->getMetaData()->get()['uid']);
            $this->logger->info(
                sprintf(
                    'Set empty canonical_link_parsed from HTTP referer to "%s" for sys_file_metadata with uid %d.',
                    $canonicalLink,
                    $file->getMetaData()->get()['uid']
                )
            );

            return $canonicalLink;
        }
        $this->logger->debug(
                sprintf(
                    'Did not added canonical_link_parsed from HTTP referer because given host (%s) did not match current host or referer points to TYPO3 backend.',
                    $refererUrlParts['host']
                )
            );

        return null;
    }

    public function updateCanonicalLinkParsedInDatabase(string $canonicalLinkParsed, int $sysFileMetadataUid): int
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $pool->getConnectionForTable('sys_file_metadata');

        $int = $connection->update('sys_file_metadata', [
            'canonical_link_parsed' => $canonicalLinkParsed,
            'tstamp' => $timestamp = time(),
        ], ['uid' => $sysFileMetadataUid]);

        $this->logger->debug(
            sprintf(
                '%s sys_file_metadata:%d - tstamp: %d, canonical_link_parsed: %s',
                $int ? 'Successfully updated' : 'Error while updating',
                $sysFileMetadataUid,
                $timestamp,
                $canonicalLinkParsed
            )
        );

        return $int;
    }

    /**
     * @return array sys_file_metadata database rows (assoc) with resolved "file" property,
     *               which contains a resolved \TYPO3\CMS\Core\Resource\File object.
     *               Also a new key "module_link" is added, to row.
     */
    public function getFilesMetadataWithSetCanonicalLink(): array
    {
        /** @var FileRepository $fileRepo */
        $fileRepo = GeneralUtility::makeInstance(FileRepository::class);

        /** @var \TYPO3\CMS\Core\Http\ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $pool->getQueryBuilderForTable('sys_file_metadata');
        /** @var Statement $result */
        $result = $queryBuilder
            ->select('*')
            ->from('sys_file_metadata')
            ->where('canonical_link_parsed != ""')
            ->orderBy('tstamp', 'DESC')
            ->execute();
        $result = $result->fetchAllAssociative();

        foreach ($result as $index => $row) {
            /** @var File $file */
            $file = $fileRepo->findByUid($row['file']);
            $result[$index]['file'] = $file;
            $result[$index]['module_link'] = $uriBuilder->buildUriFromRoute('record_edit', ['edit' => ['sys_file_metadata' => [$row['file'] => 'edit']], 'returnUrl' => (string)$request->getUri()]);
        }

        return $result;
    }
}
