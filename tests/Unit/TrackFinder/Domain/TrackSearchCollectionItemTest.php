<?php

namespace Tests\Unit\TrackFinder\Domain;

use App\TrackFinder\Domain\TrackSearchCollectionItem;
use PHPUnit\Framework\TestCase;

class TrackSearchCollectionItemTest extends TestCase
{
    public function testFromArrayCreatesTrackSearchCollectionItem(): void
    {
        $input = [
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
        ];

        $item = TrackSearchCollectionItem::fromArray($input);

        $this->assertSame('track-id-1', $item->trackId);
        $this->assertSame('Track 1', $item->track);
        $this->assertSame('Artist 1', $item->artist);
        $this->assertSame('Album 1', $item->album);
        $this->assertSame([], $item->albumImages);
        $this->assertSame('2023-01-01', $item->releaseDate);
        $this->assertSame('day', $item->releaseDatePrecision);
        $this->assertSame('http://doesNotMatter.com/track1', $item->externalUrl);
        $this->assertSame('spotify:track:1', $item->uri);
    }

    public function testFromArrayThrowsExceptionForMissingRequiredFields(): void
    {
        $input = [
            'id' => 'track-id-1',
            'name' => 'Track 1',
            'album' => [
                'name' => 'Album 1',
                'artists' => [['name' => 'Artist 1']],
            ],
            'external_urls' => ['spotify' => 'http://doesNotMatter.com/track1'],
            'uri' => 'spotify:track:1',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"release date" is empty but is required for this request');

        TrackSearchCollectionItem::fromArray($input);
    }
}
