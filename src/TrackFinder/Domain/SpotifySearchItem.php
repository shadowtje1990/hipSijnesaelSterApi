<?php

namespace App\TrackFinder\Domain;

class SpotifySearchItem
{
    private function __construct(
        public readonly string $track,
        public readonly string $artist,
        public readonly int $limit,
        public readonly int $offset,
    ) {
    }

    public static function fromSearchCriteria(array $searchCriteria): self
    {
        return new self(
            $searchCriteria['track'],
            $searchCriteria['artist'],
            $searchCriteria['limit'],
            $searchCriteria['offset'],
        );
    }
}
