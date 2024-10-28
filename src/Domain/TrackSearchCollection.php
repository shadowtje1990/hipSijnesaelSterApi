<?php

namespace App\Domain;

class TrackSearchCollection
{
    private function __construct(public readonly array $items)
    {
    }

    public static function fromArray(array $array): self
    {
        if (empty($array) || empty($array['trackSearchCollection'])) {
            return self::empty();
        }

        return new self(
            array_map(
                function (array $trackCollectionItem) {
                    return TrackSearchCollectionItem::fromArray($trackCollectionItem);
                },
                $array['trackSearchCollection']
            )
        );
    }

    public static function empty(): self
    {
        return new self([]);
    }
}
