<?php

declare(strict_types=1);

namespace Test\Unit\Utils;

use App\Utils\SpotifyAuthenticator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use App\Utils\CachedSpotifyBearerTokenProvider;

class CachedSpotifyBearerTokenProviderTest extends TestCase
{
    public function testTokenReturnsValidAccessToken()
    {
        $accessToken = 'spotify_auth_token';
        $cache = m::mock(CacheInterface::class);
        $spotifyAuthenticator = $this->createMock(SpotifyAuthenticator::class);

        $cacheItem = $this->createMock(ItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->method('get')->willReturn(null);
        $cacheItem->expects($this->once())->method('expiresAfter');
        $cacheItem->expects($this->once())->method('set');

        $cache->shouldReceive('getItem')->andReturn($cacheItem);
        $cache->shouldReceive('save');

        $spotifyAuthenticator->method('getAccessToken')->willReturn('spotify_auth_token');

        $provider = new CachedSpotifyBearerTokenProvider($cache, $spotifyAuthenticator);
        $token = $provider->token();

        $this->assertEquals($accessToken, $token);
    }

    public function testTokenReturnsCachedAccessTokenIfValid()
    {
        $cachedToken = 'spotify_auth_token';

        $cache = m::mock(CacheInterface::class);
        $spotifyAuthenticator = $this->createMock(SpotifyAuthenticator::class);

        $cacheItem = $this->createMock(ItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn($cachedToken);

        $cache->shouldReceive('getItem')->andReturn($cacheItem);

        $provider = new CachedSpotifyBearerTokenProvider($cache, $spotifyAuthenticator);
        $token = $provider->token();

        $this->assertEquals($cachedToken, $token);
    }
}
