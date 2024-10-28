<?php

namespace App\Domain;

class TrackCollection
{
    private function __construct(public readonly array $items) {}


    public static function fromArrayOfTrackCollectionItems(array $array): self
    {
        if (empty($array)) {
            return self::empty();
        }

        return new self(
            array_map(
                function(TrackCollectionItem $trackCollectionItem) {
                    return $trackCollectionItem;
                },
                $array
            )
        );
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}