<?php

namespace Tests\Unit\Services;

use App\Exceptions\SpotifyApiException;
use App\TrackFinder\Domain\SpotifySearchItem;
use App\TrackFinder\Domain\TrackSearchCollection;
use App\TrackFinder\Services\SpotifyTrackFinderService;
use App\Utils\BearerTokenProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SpotifyTrackFinderServiceTest extends TestCase
{
    private ClientInterface $spotifyWebClient;
    private BearerTokenProvider $bearerTokenProvider;
    private SpotifyTrackFinderService $spotifyTrackFinderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bearerTokenProvider = $this->createMock(BearerTokenProvider::class);
        $this->bearerTokenProvider->method('token')->willReturn('test_token');

        $this->spotifyWebClient = $this->createMock(ClientInterface::class);

        $this->spotifyTrackFinderService = new SpotifyTrackFinderService(
            $this->spotifyWebClient,
            $this->bearerTokenProvider
        );

        $this->spotifyTrackFinderService->setLogger(new NullLogger());
    }

    public function testSearchReturnsTrackSearchCollection(): void
    {
        $input = [
            'track' => 'Some Track',
            'artist' => 'Some Artist',
            'limit' => 10,
            'offset' => 5,
        ];

        $mockSearchItem = SpotifySearchItem::fromSearchCriteria($input);

        $exampleJson = json_encode([
            'id' => 'track-id-1',
            'name' => 'Track 1',
            'album' => [
                'name' => 'Album 1',
                'artists' => [['name' => 'Artist 1']],
                'release_date' => '2023-01-01',
                'release_date_precision' => 'day',
                'images' => [],
            ],
            'external_urls' => ['spotify' => 'http://doesNotMatter.com/track1'],
            'uri' => 'spotify:track:1',
        ]);
        $response = new Response(200, [], $exampleJson);
        $this->spotifyWebClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/v1/search?artist=Some Artist&q=Some Track&type=track&limit=10&offset=5')
            ->willReturn($response);

        $collection = $this->spotifyTrackFinderService->search($mockSearchItem);

        $this->assertInstanceOf(TrackSearchCollection::class, $collection);
    }

    public function testSearchHandlesSpotifyApiExceptionOnBadResponse(): void
    {
        $input = [
            'track' => 'Some Track',
            'artist' => 'Some Artist',
            'limit' => 10,
            'offset' => 5,
        ];

        $mockSearchItem = SpotifySearchItem::fromSearchCriteria($input);

        $response = new Response(400, [], '{"error": "Bad Request"}');
        $exception = new BadResponseException('Bad Request', new Request('GET', '/'), $response);

        $this->spotifyWebClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(SpotifyApiException::class);
        $this->expectExceptionMessage('Something went wrong while retrieving Spotify Track');

        $this->spotifyTrackFinderService->search($mockSearchItem);
    }

    public function testRequestThrowsExceptionWithParsedMessage(): void
    {
        $input = [
            'track' => 'Some Track',
            'artist' => 'Some Artist',
            'limit' => 10,
            'offset' => 5,
        ];

        $mockSearchItem = SpotifySearchItem::fromSearchCriteria($input);

        $response = new Response(500, [], '{"error": "Internal Server Error"}');
        $exception = new BadResponseException('Internal Server Error', new Request('GET', '/'), $response);

        $this->spotifyWebClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(SpotifyApiException::class);
        $this->expectExceptionMessage('Something went wrong while retrieving Spotify Track');

        $this->spotifyTrackFinderService->search($mockSearchItem);
    }
}
