<?php

declare(strict_types=1);

namespace Test\Unit\Controller;

use App\Controller\PlaylistApiController;
use App\Domain\TrackCollection;
use App\TrackFinder\Domain\TrackSearchCollection;
use App\TrackFinder\Services\TrackFinderServiceInterface;
use App\Transformer\TrackCollectionOutputTransformer;
use App\Validators\TrackCollectionValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends TestCase
{
    private TrackFinderServiceInterface $trackFinderService;
    private TrackCollectionValidator $validator;
    private TrackCollectionOutputTransformer $collectionOutputTransformer;
    private PlaylistApiController $controller;

    protected function setUp(): void
    {
        $this->trackFinderService = $this->createMock(TrackFinderServiceInterface::class);
        $this->validator = $this->createMock(TrackCollectionValidator::class);
        $this->collectionOutputTransformer = $this->createMock(TrackCollectionOutputTransformer::class);

        $this->controller = new PlaylistApiController(
            $this->trackFinderService,
            $this->validator,
            $this->collectionOutputTransformer
        );
    }

    public function testGetTrackCollectionFromRequestedTracksReturnsExpectedResponse()
    {
        $inputData = [
            'trackSearchCollection' => [
                ['artist' => 'Darude', 'track' => 'Sandstorm'],
                ['artist' => 'Linkin Park', 'track' => 'In the end'],
            ],
        ];

        $expectedOutput = [
            'trackCollection' => [
                [
                    'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                    'track' => 'Sandstorm',
                    'artist' => 'Darude',
                    'releaseDate' => '2001-01-01',
                    'releaseDatePrecision' => 'day',
                    'externalUrl' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                    'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                ],
                [
                    'id' => '60a0Rd6pjrkxjPbaKzXjfq',
                    'track' => 'In the End',
                    'artist' => 'Linkin Park',
                    'releaseDate' => '2000',
                    'releaseDatePrecision' => 'year',
                    'externalUrl' => 'https://open.spotify.com/track/60a0Rd6pjrkxjPbaKzXjfq',
                    'uri' => 'spotify:track:60a0Rd6pjrkxjPbaKzXjfq',
                ],
            ],
        ];

        $request = new Request([], [], [], [], [], [], json_encode($inputData));

        $this->validator->expects($this->once())
            ->method('validateTrackCollection')
            ->with($inputData);

        $trackSearchCollection = TrackSearchCollection::fromArray($inputData);
        $trackCollection = $this->createMock(TrackCollection::class);

        $this->trackFinderService->expects($this->once())
            ->method('getTrackCollectionFromTrackSearchCollection')
            ->with($trackSearchCollection)
            ->willReturn($trackCollection);

        $this->collectionOutputTransformer->expects($this->once())
            ->method('transformTrackCollection')
            ->with($trackCollection)
            ->willReturn($expectedOutput);

        $response = $this->controller->getTrackCollectionFromRequestedTracks($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedOutput), $response->getContent());
    }
}
