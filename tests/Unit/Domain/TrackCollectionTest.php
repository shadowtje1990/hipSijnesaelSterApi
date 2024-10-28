<?php

declare(strict_types=1);

namespace Test\Unit\Domain;

use App\Domain\TrackCollection;
use App\Domain\TrackCollectionItem;
use PHPUnit\Framework\TestCase;

class TrackCollectionTest extends TestCase
{
    public function testFromArrayOfTrackCollectionItemsCreatesCollection(): void
    {
        $trackItems = [
            TrackCollectionItem::fromSpotifyJson(json_encode([
                'tracks' => [
                    'items' => [
                        [
                            'id' => '1',
                            'name' => 'Track 1',
                            'album' => [
                                'artists' => [['name' => 'Artist 1']],
                                'release_date' => '2023-01-01',
                                'release_date_precision' => 'day',
                            ],
                            'external_urls' => ['spotify' => 'https://open.spotify.com/track/1'],
                            'uri' => 'spotify:track:1',
                        ],
                    ],
                ],
            ])),
            TrackCollectionItem::fromSpotifyJson(json_encode([
                'tracks' => [
                    'items' => [
                        [
                            'id' => '2',
                            'name' => 'Track 2',
                            'album' => [
                                'artists' => [['name' => 'Artist 2']],
                                'release_date' => '2023-01-01',
                                'release_date_precision' => 'day',
                            ],
                            'external_urls' => ['spotify' => 'https://open.spotify.com/track/2'],
                            'uri' => 'spotify:track:2',
                        ],
                    ],
                ],
            ])),
        ];

        $collection = TrackCollection::fromArrayOfTrackCollectionItems($trackItems);

        $this->assertCount(2, $collection->items);
        $this->assertSame('1', $collection->items[0]->trackId);
        $this->assertSame('Track 1', $collection->items[0]->track);
        $this->assertSame('Artist 1', $collection->items[0]->artist);
    }

    public function testFromArrayOfTrackCollectionItemsReturnsEmptyCollection(): void
    {
        $collection = TrackCollection::fromArrayOfTrackCollectionItems([]);
        $this->assertTrue($collection->isEmpty());
    }

    public function testEmptyReturnsEmptyCollection(): void
    {
        $collection = TrackCollection::empty();
        $this->assertTrue($collection->isEmpty());
    }

    public function testIsEmptyReturnsCorrectValue(): void
    {
        $trackItems = [
            TrackCollectionItem::fromSpotifyJson(json_encode([
                'tracks' => [
                    'items' => [
                        [
                            'id' => '1',
                            'name' => 'Track 1',
                            'album' => [
                                'artists' => [['name' => 'Artist 1']],
                                'release_date' => '2023-01-01',
                                'release_date_precision' => 'day',
                            ],
                            'external_urls' => ['spotify' => 'https://open.spotify.com/track/1'],
                            'uri' => 'spotify:track:1',
                        ],
                    ],
                ],
            ])),
        ];
        $collection = TrackCollection::fromArrayOfTrackCollectionItems($trackItems);
        $this->assertFalse($collection->isEmpty());
        $emptyCollection = TrackCollection::empty();
        $this->assertTrue($emptyCollection->isEmpty());
    }
}
