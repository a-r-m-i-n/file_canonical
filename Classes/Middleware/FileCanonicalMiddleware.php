<?php declare(strict_types = 1);
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
use Symfony\Component\Mime\MimeTypes;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileCanonicalMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestedFile = Environment::getPublicPath() . $request->getUri()->getPath();

        if (file_exists($requestedFile) && is_file($requestedFile)) {
            $uri = $request->getUri()->getPath();

            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $defaultStorage = $resourceFactory->getDefaultStorage();
            if ($defaultStorage) {
                $storagePublicUrl = $defaultStorage->getRootLevelFolder()->getPublicUrl();
                $currentFileIdentifier = substr($uri, strlen($storagePublicUrl));
                try {
                $file = $defaultStorage->getFile($currentFileIdentifier);
                } catch (\InvalidArgumentException $e) {
                    return $handler->handle($request); // Early return
                }

                if ($file->hasProperty('canonical_link_parsed')) {
                    $canonicalLink = $file->getProperty('canonical_link_parsed');

                    $mimeType = new MimeTypes();
                    $mimeType = $mimeType->guessMimeType($requestedFile);

                    $response = new Response();
                    $response = $response
                        ->withHeader('Content-Length', (string)filesize($requestedFile))
                        ->withHeader('Content-Type', $mimeType)
                        ->withBody(new Stream($requestedFile))
                    ;

                    if (!empty($canonicalLink)) {
                        // TODO: Add canonical link by HTTP referrer, if enabled and if host matches
                        return $response->withHeader('Link', '<' . $canonicalLink . '>; rel="canonical"');
                    }
                    return $response;
                }
            }
        }

        return $handler->handle($request);
    }
}
