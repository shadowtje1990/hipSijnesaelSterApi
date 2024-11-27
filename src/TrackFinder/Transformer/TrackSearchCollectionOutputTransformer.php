<?php

namespace App\TrackFinder\Transformer;

use App\Domain\TrackCollection;
use App\Domain\TrackCollectionItem;
use App\TrackFinder\Domain\TrackSearchCollection;
use App\TrackFinder\Domain\TrackSearchCollectionItem;

class TrackSearchCollectionOutputTransformer
{
    public function transformTrackSearchCollection(TrackSearchCollection $trackCollection): array
    {
        return [
            'trackSearchCollection' => array_map(
                function (TrackSearchCollectionItem $trackSearchCollectionItem) {
                    return [
                        'id' => $trackSearchCollectionItem->trackId,
                        'track' => $trackSearchCollectionItem->track,
                        'artist' => $trackSearchCollectionItem->artist,
                        'album' => $trackSearchCollectionItem->album,
                        'images' => $trackSearchCollectionItem->albumImages,
                        'releaseDate' => $trackSearchCollectionItem->releaseDate,
                        'releaseDatePrecision' => $trackSearchCollectionItem->releaseDatePrecision,
                        'externalUrl' => $trackSearchCollectionItem->externalUrl,
                        'uri' => $trackSearchCollectionItem->uri,
                    ];
                }, $trackCollection->items
            ),
            'metadata' => $trackCollection->metadata->toArray(),
        ];
    }
}
