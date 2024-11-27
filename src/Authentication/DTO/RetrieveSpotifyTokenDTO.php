<?php

namespace App\Authentication\DTO;

class RetrieveSpotifyTokenDTO
{
    private function __construct(public readonly string $code)
    {
    }

    public static function fromSearchCriteria(array $searchCriteria): self
    {
        return new self($searchCriteria['code']);
    }
}
