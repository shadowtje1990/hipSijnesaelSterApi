<?php

namespace App\Domain;

class TrackSearchCollectionItem
{
    private function __construct(public readonly string $track, public readonly string $artist)
    {
    }

    public static function fromArray(array $trackCollectionItem): self
    {
        return new self($trackCollectionItem['track'], $trackCollectionItem['artist']);
    }
}
