<?php

namespace App\TrackFinder\Domain;

class TrackSearchCollectionItem
{
    private function __construct(
        public readonly string $trackId,
        public readonly string $track,
        public readonly string $artist,
        public readonly string $album,
        public readonly array $albumImages,
        public readonly string $releaseDate,
        public readonly string $releaseDatePrecision,
        public readonly string $externalUrl,
        public readonly string $uri,
    ) {
    }

    public static function fromArray(array $array): self
    {
        self::makeSureThatTheRequestContainsTheExpectedValue($array);

        return new self(
            $array['id'],
            $array['name'],
            !empty($array['album']['artists'][0]['name']) ? $array['album']['artists'][0]['name'] : '',
            $array['album']['name'],
            $array['album']['images'],
            $array['album']['release_date'],
            $array['album']['release_date_precision'],
            $array['external_urls']['spotify'],
            $array['uri']
        );
    }

    private static function makeSureThatTheRequestContainsTheExpectedValue(array $array)
    {
        if (empty($array['id'])) {
            throw new \RuntimeException('"id" is empty but is required for this request');
        }

        if (empty($array['name'])) {
            throw new \RuntimeException('"name" is empty but is required for this request');
        }

        if (empty($array['album']['name'])) {
            throw new \RuntimeException('"album name" is empty but is required for this request');
        }

        if (empty($array['album']['artists'][0]['name'])) {
            throw new \RuntimeException('"artist name" is empty but is required for this request');
        }

        if (empty($array['album']['release_date'])) {
            throw new \RuntimeException('"release date" is empty but is required for this request');
        }

        if (empty($array['album']['release_date_precision'])) {
            throw new \RuntimeException('"release date precision" is empty but is required for this request');
        }

        if (empty($array['external_urls']['spotify'])) {
            throw new \RuntimeException('"external_urls spotify" is empty but is required for this request');
        }

        if (empty($array['uri'])) {
            throw new \RuntimeException('"uri" is empty but is required for this request');
        }
    }
}
