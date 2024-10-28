<?php

namespace App\Domain;

class TrackCollectionItem
{
    private function __construct(
        public readonly string $trackId,
        public readonly string $track,
        public readonly string $artist,
        public readonly string $releaseDate,
        public readonly string $releaseDatePrecision,
        public readonly string $externalUrl,
        public readonly string $uri,
    ) {
    }

    public static function fromSpotifyJson(string $json): self
    {
        $array = json_decode($json, true);
        if (empty($array['tracks']['items'][0])) {
            throw new \RuntimeException('"track" item is empty but is required for this request');
        }

        $trackItem = $array['tracks']['items'][0];
        self::makeSureThatTheRequestContainsTheExpectedValue($trackItem);

        return new self(
            $trackItem['id'],
            $trackItem['name'],
            $trackItem['album']['artists'][0]['name'],
            $trackItem['album']['release_date'],
            $trackItem['album']['release_date_precision'],
            $trackItem['external_urls']['spotify'],
            $trackItem['uri']
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
