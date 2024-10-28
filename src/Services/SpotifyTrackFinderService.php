<?php

namespace App\Services;

use App\Domain\TrackCollection;
use App\Domain\TrackCollectionItem;
use App\Domain\TrackSearchCollection;
use App\Domain\TrackSearchCollectionItem;
use App\Exceptions\SpotifyApiException;
use App\Utils\BearerTokenProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SpotifyTrackFinderService implements TrackFinderServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ClientInterface $spotifyWebClient,
        private readonly BearerTokenProvider $bearerTokenProvider,
    ) {
    }

    public function getTrackCollectionFromTrackSearchCollection(TrackSearchCollection $trackSearchCollection): TrackCollection
    {
        $items = [];
        foreach ($trackSearchCollection->items as $trackSearchCollectionItem) {
            $items[] = $this->getTrackCollectionFromTrackSearchCollectionItem($trackSearchCollectionItem);
        }

        if (empty($items)) {
            return TrackCollection::empty();
        }

        return TrackCollection::fromArrayOfTrackCollectionItems($items);
    }

    private function getTrackCollectionFromTrackSearchCollectionItem(TrackSearchCollectionItem $trackSearchCollectionItem): TrackCollectionItem
    {
        $query = sprintf('?artist=%s&q=%s&type=track&limit=1', $trackSearchCollectionItem->artist, $trackSearchCollectionItem->track);
        $response = $this->request('GET', sprintf('/v1/search%s', $query));

        //        $query = sprintf("track:")
        if (200 !== $response->getStatusCode()) {
            throw new SpotifyApiException(sprintf('Unexpected Spotify response on getUrlsFromTrackCollection with Code: %s and Body: %s', $response->getStatusCode(), (string) $response->getBody()));
        }

        return TrackCollectionItem::fromSpotifyJson((string) $response->getBody());
    }

    private function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        try {
            $options = $this->appendBearerToken($options);

            return $this->spotifyWebClient->request($method, $uri, $options);
        } catch (BadResponseException $exception) {
            throw $this->throwExceptionWithParsedMessage($exception);
        }
    }

    private function throwExceptionWithParsedMessage(BadResponseException $exception): SpotifyApiException
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $responseBodyAsString = (string) $exception->getResponse()->getBody();
        $responseMessage = $exception->getResponse()->getReasonPhrase();

        $message = sprintf(
            'Unexpected Spotify response. Code: %s | Message: %s | Body: %s',
            $statusCode,
            $responseMessage,
            !empty($responseBodyAsString) ? $responseBodyAsString : '?'
        );

        $this->logMessageAsError($message);

        return new SpotifyApiException('Something went wrong while retrieving Spotify Track', $statusCode);
    }

    private function logMessageAsError(string $message): void
    {
        $this->logger->error($message);
    }

    private function appendBearerToken(array $options): array
    {
        $headers['Authorization'] = "Bearer {$this->bearerTokenProvider->token()}";

        return array_merge($options, ['headers' => $headers]);
    }
}
