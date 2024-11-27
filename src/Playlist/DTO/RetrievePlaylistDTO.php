<?php

namespace App\Playlist\DTO;

use App\Playlist\Domain\NameIdentifier;

class RetrievePlaylistDTO
{
    private function __construct(public readonly NameIdentifier $nameIdentifier, public readonly string $originalName)
    {
    }

    public static function fromSearchCriteria(array $searchCriteria): self
    {
        return new self(NameIdentifier::fromString($searchCriteria['name']), $searchCriteria['name']);
    }
}
