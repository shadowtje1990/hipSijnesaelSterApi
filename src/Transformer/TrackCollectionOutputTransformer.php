<?php

namespace App\Transformer;

use App\Domain\TrackCollection;
use App\Domain\TrackCollectionItem;

class TrackCollectionOutputTransformer
{
    public function transformTrackCollection(TrackCollection $trackCollection): array
    {
        return [
            'trackCollection' => array_map(
                function (TrackCollectionItem $trackCollectionItem) {
                    return [
                        'id' => $trackCollectionItem->trackId,
                        'track' => $trackCollectionItem->track,
                        'artist' => $trackCollectionItem->artist,
                        'releaseDate' =>$trackCollectionItem->releaseDate,
                        'releaseDatePrecision' =>$trackCollectionItem->releaseDatePrecision,
                        'externalUrl' =>$trackCollectionItem->externalUrl,
                        'uri' =>$trackCollectionItem->uri,
                    ];
                }, $trackCollection->items
            )
        ];
    }
}