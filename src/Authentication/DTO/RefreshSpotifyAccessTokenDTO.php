<?php

namespace App\Authentication\DTO;

class RefreshSpotifyAccessTokenDTO
{
    private function __construct(public readonly string $refreshToken)
    {
    }

    public static function fromSearchCriteria(array $searchCriteria): self
    {
        return new self($searchCriteria['refreshToken']);
    }
}
