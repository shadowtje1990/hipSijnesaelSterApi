<?php

declare(strict_types=1);

namespace Test\Unit\Transformer;

use App\Domain\TrackCollection;
use App\Domain\TrackCollectionItem;
use App\Transformer\TrackCollectionOutputTransformer;
use PHPUnit\Framework\TestCase;

class TrackCollectionOutputTransformerTest extends TestCase
{
    public function testTransformTrackCollection()
    {
        $jsonTrackMock = file_get_contents(__DIR__.'/../../Responses/Mocked/get_search_track_darude_sandstorm.json');
        $jsonTrack2Mock = file_get_contents(__DIR__.'/../../Responses/Mocked/get_search_track_linkin_park_in_the_end.json');

        $trackCollection = TrackCollection::fromArrayOfTrackCollectionItems([
            TrackCollectionItem::fromSpotifyJson($jsonTrackMock),
            TrackCollectionItem::fromSpotifyJson($jsonTrack2Mock),
        ]);

        $transformer = new TrackCollectionOutputTransformer();

        $result = $transformer->transformTrackCollection($trackCollection);

        $expected = [
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

        $this->assertEquals($expected, $result);
    }
}
