<?php

namespace App\Playlist\DTO;

use App\Playlist\Domain\NameIdentifier;

class UpdatePlaylistDTO
{
    private function __construct(
        public readonly NameIdentifier $nameIdentifier,
        public readonly string $originalName,
        public readonly array $playlist,
    ) {
    }

    public static function fromSearchCriteria(array $searchCriteria): self
    {
        return new self(
            NameIdentifier::fromString($searchCriteria['name']),
            $searchCriteria['name'],
            $searchCriteria['playlist']
        );
    }

    public function toJson(): string
    {
        $array = [
            'nameIdentifier' => $this->nameIdentifier->value,
            'originalName' => $this->originalName,
            'playlist' => $this->playlist,
        ];

        return json_encode($array);
    }
}
