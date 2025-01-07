<?php

namespace Tests\Unit\TrackFinder\Domain;

use App\TrackFinder\Domain\TrackSearchCollection;
use App\TrackFinder\Domain\Metadata;
use PHPUnit\Framework\TestCase;

class TrackSearchCollectionTest extends TestCase
{
    public function testFromSpotifyJsonCreatesTrackSearchCollection(): void
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
            ],
        ]);

        $collection = TrackSearchCollection::fromSpotifyJson($json);

        $this->assertCount(1, $collection->items);
        $this->assertInstanceOf(Metadata::class, $collection->metadata);
    }

    public function testFromSpotifyJsonHandlesEmptyJson(): void
    {
        $json = '{}';

        $collection = TrackSearchCollection::fromSpotifyJson($json);

        $this->assertCount(0, $collection->items);
        $this->assertInstanceOf(Metadata::class, $collection->metadata);
    }

    public function testFromSpotifyJsonHandlesMissingTracks(): void
    {
        $json = json_encode(['tracks' => []]);

        $collection = TrackSearchCollection::fromSpotifyJson($json);

        $this->assertCount(0, $collection->items);
        $this->assertInstanceOf(Metadata::class, $collection->metadata);
    }
}
