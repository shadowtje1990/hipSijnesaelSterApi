<?php

namespace App\Tests\Domain;

use App\Domain\TrackCollectionItem;
use PHPUnit\Framework\TestCase;

class TrackCollectionItemTest extends TestCase
{
    public function testFromSpotifyJsonCreatesTrackCollectionItem(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $trackItem = TrackCollectionItem::fromSpotifyJson($json);

        $this->assertInstanceOf(TrackCollectionItem::class, $trackItem);
        $this->assertSame('6Sy9BUbgFse0n0LPA5lwy5', $trackItem->trackId);
        $this->assertSame('Sandstorm', $trackItem->track);
        $this->assertSame('Darude', $trackItem->artist);
        $this->assertSame('2001-01-01', $trackItem->releaseDate);
        $this->assertSame('day', $trackItem->releaseDatePrecision);
        $this->assertSame('https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5', $trackItem->externalUrl);
        $this->assertSame('spotify:track:6Sy9BUbgFse0n0LPA5lwy5', $trackItem->uri);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenItemIsEmpty(): void
    {
        $json = json_encode(['tracks' => ['items' => []]]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"track" item is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingId(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"id" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingName(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"name" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingArtistName(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => ''],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"artist name" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingReleaseDate(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"release date" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingReleaseDatePrecision(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => '',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"release date precision" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingExternalUrl(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => '',
                        ],
                        'uri' => 'spotify:track:6Sy9BUbgFse0n0LPA5lwy5',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"external_urls spotify" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }

    public function testFromSpotifyJsonThrowsExceptionWhenMissingUri(): void
    {
        $json = json_encode([
            'tracks' => [
                'items' => [
                    [
                        'id' => '6Sy9BUbgFse0n0LPA5lwy5',
                        'name' => 'Sandstorm',
                        'album' => [
                            'artists' => [
                                ['name' => 'Darude'],
                            ],
                            'release_date' => '2001-01-01',
                            'release_date_precision' => 'day',
                        ],
                        'external_urls' => [
                            'spotify' => 'https://open.spotify.com/track/6Sy9BUbgFse0n0LPA5lwy5',
                        ],
                        'uri' => '',
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"uri" is empty but is required for this request');

        TrackCollectionItem::fromSpotifyJson($json);
    }
}
