<?php

declare(strict_types=1);

namespace Test\Unit\Services;

use App\Domain\TrackCollection;
use App\Exceptions\SpotifyApiException;
use App\TrackFinder\Domain\TrackSearchCollection;
use App\TrackFinder\Services\SpotifyTrackFinderService;
use App\Utils\BearerTokenProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SpotifyTrackFinderServiceTest extends TestCase
{
    private ClientInterface $spotifyWebClient;
    private BearerTokenProvider $bearerTokenProvider;
    private SpotifyTrackFinderService $service;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->spotifyWebClient = $this->createMock(ClientInterface::class);
        $this->bearerTokenProvider = $this->createMock(BearerTokenProvider::class);
        $this->service = new SpotifyTrackFinderService(
            $this->spotifyWebClient,
            $this->bearerTokenProvider
        );
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service->setLogger($this->logger);
    }

    public function testGetTrackCollectionFromTrackSearchCollectionReturnsTrackCollection()
    {
        $trackSearchCollection = TrackSearchCollection::fromArray(
            [
                'trackSearchCollection' => [['artist' => 'Darude', 'track' => 'sandstorm']],
            ]);

        $responseBody = file_get_contents(__DIR__.'/../../Responses/Mocked/get_search_track_darude_sandstorm.json');

        $response = new Response(200, [], $responseBody);

        $this->spotifyWebClient->expects($this->once())
            ->method('request')
            ->with('GET', '/v1/search?artist=Darude&q=sandstorm&type=track&limit=1', $this->anything())
            ->willReturn($response);

        $trackCollection = $this->service->getTrackCollectionFromTrackSearchCollection($trackSearchCollection);
        $this->assertInstanceOf(TrackCollection::class, $trackCollection);
        $this->assertCount(1, $trackCollection->items);
        $this->assertEquals('6Sy9BUbgFse0n0LPA5lwy5', $trackCollection->items[0]->trackId);
    }

    public function testGetTrackCollectionFromTrackSearchCollectionHandlesEmptyCollection()
    {
        $trackSearchCollection = TrackSearchCollection::fromArray([]);

        $trackCollection = $this->service->getTrackCollectionFromTrackSearchCollection($trackSearchCollection);

        $this->assertInstanceOf(TrackCollection::class, $trackCollection);
        $this->assertTrue($trackCollection->isEmpty());
    }

    public function testGetTrackCollectionFromTrackSearchCollectionThrowsExceptionOnBadResponse()
    {
        $trackSearchCollection = TrackSearchCollection::fromArray(
            [
                'trackSearchCollection' => [['artist' => 'Darude', 'track' => 'sandstorm']],
            ]);

        $request = new Request('GET', '/v1/search');

        $response = new Response(404, [], 'Not Found');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Unexpected Spotify response.'));

        $this->spotifyWebClient->expects($this->once())
            ->method('request')
            ->willThrowException(new BadResponseException('something went wrong', $request, $response));

        $this->expectException(SpotifyApiException::class);
        $this->expectExceptionMessage('Something went wrong while retrieving Spotify Track');

        $this->service->getTrackCollectionFromTrackSearchCollection($trackSearchCollection);
    }
}
