<?php

declare(strict_types = 1);

namespace T3\FileCanonical\Middleware;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use T3\FileCanonical\FileCanonicalManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileCanonicalMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestedFile = Environment::getPublicPath() . $request->getUri()->getPath();
        if (!file_exists($requestedFile) || !is_file($requestedFile)) {
            return $handler->handle($request); // Early return
        }

        /** @var FileCanonicalManager $manager */
        $manager = GeneralUtility::makeInstance(FileCanonicalManager::class);
        $uri = $request->getUri()->getPath();
        $file = $manager->getFileFromUri($uri);

        if (!$file || !$file instanceof File || !$file->hasProperty('canonical_link_parsed')) {
            return $handler->handle($request); // Early return
        }

        $canonicalLink = $file->getProperty('canonical_link_parsed');
        $response = $manager->buildResponseForFile($requestedFile);

        if (empty($canonicalLink) &&
            $request->hasHeader('referer') &&
            $manager->getConfig()->isAutoCreateFileCanonicalFromRefererEnabled()
        ) {
            $manager->checkCanonicalUrlFromRefererAndUpdateMetadata($file, $request);
        }

        if (!empty($canonicalLink)) {
            return $response->withHeader('Link', '<' . $canonicalLink . '>; rel="canonical"');
        }

        return $response;
    }
}
