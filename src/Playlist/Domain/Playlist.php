<?php

namespace App\Playlist\Domain;

class Playlist
{
    private function __construct(
        public readonly NameIdentifier $fileNameIdentifier,
        public readonly string $name,
        public readonly array $tracks,
    ) {
    }

    public static function create(string $jsonPlaylistData): self
    {
        $data = json_decode($jsonPlaylistData, true);

        return new self(
            !empty($data['nameIdentifier']) ? NameIdentifier::fromString($data['nameIdentifier']) : NameIdentifier::empty(),
            $data['originalName'] ?? '',
            $data['playlist'] ?? ''
        );
    }

    public static function empty(): self
    {
        return new self(NameIdentifier::empty(), '', []);
    }
}
