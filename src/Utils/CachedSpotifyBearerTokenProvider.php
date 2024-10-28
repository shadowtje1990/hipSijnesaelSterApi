<?php

declare(strict_types=1);

namespace App\Utils;

use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedSpotifyBearerTokenProvider implements BearerTokenProvider
{
    public const ACCESS_TOKEN_EXPIRE_TIME_IN_SECONDS = 3600;
    public const SPOTIFY_TOKEN_KEY = 'spotify_auth_token';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly SpotifyAuthenticator $spotifyAuthenticator,
    ) {
    }

    public function token(): string
    {
        /* @var ItemInterface $cachedItem */
        $cachedItem = $this->cache->getItem(self::SPOTIFY_TOKEN_KEY);
        $cachedItem->expiresAfter(self::ACCESS_TOKEN_EXPIRE_TIME_IN_SECONDS);

        $tokenAccessor = \Closure::bind(function (CacheItemInterface $cachedItem): string {
            $token = $this->spotifyAuthenticator->getAccessToken();
            $cachedItem->set($token);
            $this->cache->save($cachedItem);

            return $token;
        }, $this);

        return $cachedItem->isHit() ? $cachedItem->get() : $tokenAccessor($cachedItem);
    }
}
