<?php

namespace Tests\Unit\TrackFinder\Domain;

use App\TrackFinder\Domain\SpotifySearchItem;
use PHPUnit\Framework\TestCase;

class SpotifySearchItemTest extends TestCase
{
    public function testFromSearchCriteriaCreatesSpotifySearchItem(): void
    {
        $input = [
            'track' => 'Some Track',
            'artist' => 'Some Artist',
            'limit' => 10,
            'offset' => 5,
        ];

        $searchItem = SpotifySearchItem::fromSearchCriteria($input);

        $this->assertSame('Some Track', $searchItem->track);
        $this->assertSame('Some Artist', $searchItem->artist);
        $this->assertSame(10, $searchItem->limit);
        $this->assertSame(5, $searchItem->offset);
    }

    public function testFromSearchCriteriaHandlesEmptyValues(): void
    {
        $input = [
            'track' => '',
            'artist' => '',
            'limit' => 0,
            'offset' => 0,
        ];

        $searchItem = SpotifySearchItem::fromSearchCriteria($input);

        $this->assertSame('', $searchItem->track);
        $this->assertSame('', $searchItem->artist);
        $this->assertSame(0, $searchItem->limit);
        $this->assertSame(0, $searchItem->offset);
    }
}
