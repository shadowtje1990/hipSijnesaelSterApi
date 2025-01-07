<?php

namespace Tests\Unit\TrackFinder\Transformer;

use App\TrackFinder\Domain\Metadata;
use App\TrackFinder\Domain\TrackSearchCollection;
use App\TrackFinder\Transformer\TrackSearchCollectionOutputTransformer;
use PHPUnit\Framework\TestCase;

class TrackSearchCollectionOutputTransformerTest extends TestCase
{
    private TrackSearchCollectionOutputTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transformer = new TrackSearchCollectionOutputTransformer();
    }

    public function testTransformTrackSearchCollection(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
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
                    ],
                ],
                'limit' => 1,
                'offset' => 0,
                'total' => 1,
                'current' => '',
                'next' => '',
                'previous' => '',
            ],
        ]);

        $trackCollection = TrackSearchCollection::fromSpotifyJson($json);

        $expected = [
            'trackSearchCollection' => [
                [
                    'id' => 'track-id-1',
                    'track' => 'Track 1',
                    'artist' => 'Artist 1',
                    'album' => 'Album 1',
                    'images' => [],
                    'releaseDate' => '2023-01-01',
                    'releaseDatePrecision' => 'day',
                    'externalUrl' => 'http://doesNotMatter.com/track1',
                    'uri' => 'spotify:track:1',
                ],
            ],
            'metadata' => [
                'limit' => 1,
                'offset' => 0,
                'total' => 1,
                'current' => '',
                'next' => '',
                'previous' => '',
            ],
        ];

        $result = $this->transformer->transformTrackSearchCollection($trackCollection);

        $this->assertSame($expected, $result);
    }

    public function testEmptyTrackSearchCollection(): void
    {
        $trackCollection = TrackSearchCollection::empty();

        $expected = [
            'trackSearchCollection' => [],
            'metadata' => [
                'limit' => 0,
                'offset' => 0,
                'total' => 0,
                'current' => null,
                'next' => null,
                'previous' => null,
            ],
        ];

        $result = $this->transformer->transformTrackSearchCollection($trackCollection);

        $this->assertSame($expected, $result);
    }
}
