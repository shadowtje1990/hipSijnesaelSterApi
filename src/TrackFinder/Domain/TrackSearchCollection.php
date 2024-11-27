<?php

namespace App\TrackFinder\Domain;

class TrackSearchCollection
{
    private function __construct(public readonly array $items, public readonly Metadata $metadata)
    {
    }

    public static function fromSpotifyJson(string $json): self
    {
        $array = json_decode($json, true);
        if (empty($array) || empty($array['tracks']) || empty($array['tracks']['items'])) {
            return self::empty();
        }

        return new self(
            array_map(
                function (array $trackSearchCollectionItem) {
                    return TrackSearchCollectionItem::fromArray($trackSearchCollectionItem);
                },
                $array['tracks']['items']
            ),
            Metadata::fromArray($array)
        );
    }

    public static function empty(): self
    {
        return new self([], Metadata::empty());
    }
}
