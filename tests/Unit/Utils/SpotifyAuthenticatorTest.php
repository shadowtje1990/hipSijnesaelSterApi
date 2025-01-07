<?php

namespace Tests\Unit\Utils;

use App\Exceptions\SpotifyAuthenticatorException;
use App\Utils\SpotifyAuthenticator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class SpotifyAuthenticatorTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testGetAccessTokenReturnsAccessToken(): void
    {
        $mockClient = \Mockery::mock(ClientInterface::class);
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockStream = \Mockery::mock(StreamInterface::class);

        $mockResponse->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $mockResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($mockStream);

        $mockStream->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode(['access_token' => 'test_access_token']));

        $mockClient->shouldReceive('request')
            ->with('POST', 'https://accounts.spotify.com/api/token', \Mockery::on(function ($options) {
                return isset($options['headers']['Authorization'])
                    && isset($options['headers']['Content-Type'])
                    && 'client_credentials' === $options['form_params']['grant_type'];
            }))
            ->once()
            ->andReturn($mockResponse);

        $logger = \Mockery::mock(LoggerInterface::class);

        $authenticator = new SpotifyAuthenticator($mockClient, 'client_id', 'client_secret');
        $authenticator->setLogger($logger);
        $accessToken = $authenticator->getAccessToken();
        $this->assertSame('test_access_token', $accessToken);
    }

    public function testGetAccessTokenThrowsExceptionOnInvalidStatusCode(): void
    {
        $mockClient = \Mockery::mock(ClientInterface::class);
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockStream = \Mockery::mock(StreamInterface::class);

        $mockResponse->shouldReceive('getStatusCode')
            ->twice()
            ->andReturn(400);

        $mockResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($mockStream);

        $mockStream->shouldReceive('__toString')
            ->once()
            ->andReturn('{"error": "invalid_request"}');

        $mockClient->shouldReceive('request')
            ->once()
            ->andReturn($mockResponse);

        $logger = \Mockery::mock(LoggerInterface::class);

        $authenticator = new SpotifyAuthenticator($mockClient, 'client_id', 'client_secret');
        $authenticator->setLogger($logger);
        $this->expectException(SpotifyAuthenticatorException::class);
        $this->expectExceptionMessage('Unexpected SpotifyAuthenticator response with Code: 400');

        $authenticator->getAccessToken();
    }

    public function testGetAccessTokenThrowsExceptionOnMissingAccessToken(): void
    {
        $mockClient = \Mockery::mock(ClientInterface::class);
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockStream = \Mockery::mock(StreamInterface::class);

        $mockResponse->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $mockResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($mockStream);

        $mockStream->shouldReceive('__toString')
            ->once()
            ->andReturn(json_encode([]));

        $mockClient->shouldReceive('request')
            ->once()
            ->andReturn($mockResponse);

        $logger = \Mockery::mock(LoggerInterface::class);

        $authenticator = new SpotifyAuthenticator($mockClient, 'client_id', 'client_secret');
        $authenticator->setLogger($logger);

        $this->expectException(SpotifyAuthenticatorException::class);
        $this->expectExceptionMessage('Missing access token from Spotify authenticator response');

        $authenticator->getAccessToken();
    }

    public function testGetAccessTokenHandlesBadResponseException(): void
    {
        $mockClient = \Mockery::mock(ClientInterface::class);
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockStream = \Mockery::mock(StreamInterface::class);

        $mockResponse->shouldReceive('getStatusCode')
            ->andReturn(401);

        $mockResponse->shouldReceive('getReasonPhrase')
            ->andReturn('Unauthorized');

        $mockResponse->shouldReceive('getBody')
            ->andReturn($mockStream);

        $mockStream->shouldReceive('__toString')
            ->andReturn('{"error": "invalid_client"}');

        $exception = \Mockery::mock(BadResponseException::class);
        $exception->shouldReceive('getResponse')
            ->andReturn($mockResponse);

        $mockClient->shouldReceive('request')
            ->once()
            ->andThrow($exception);

        $logger = \Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->once();

        $authenticator = new SpotifyAuthenticator($mockClient, 'client_id', 'client_secret');
        $authenticator->setLogger($logger);

        $this->expectException(SpotifyAuthenticatorException::class);
        $this->expectExceptionMessage('Something went wrong while retrieving the Spotify bearerToken');

        $authenticator->getAccessToken();
    }
}
