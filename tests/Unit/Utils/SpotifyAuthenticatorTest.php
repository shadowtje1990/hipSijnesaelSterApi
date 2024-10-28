<?php

declare(strict_types=1);

namespace Test\Unit\Utils;

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
    private string $clientId = 'test_client_id';
    private string $clientSecret = 'test_client_secret';
    private ClientInterface $client;
    private LoggerInterface $logger;
    private SpotifyAuthenticator $spotifyAuthenticator;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->spotifyAuthenticator = new SpotifyAuthenticator($this->client, $this->clientId, $this->clientSecret);
        $this->spotifyAuthenticator->setLogger($this->logger);
    }

    public function testGetAccessTokenReturnsTokenOnSuccessfulResponse()
    {
        $accessToken = 'valid_access_token';
        $response = $this->createMock(ResponseInterface::class);
        $responseBody = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($responseBody);
        $responseBody->method('__toString')->willReturn(json_encode(['access_token' => $accessToken]));

        $this->client->method('request')->willReturn($response);

        $token = $this->spotifyAuthenticator->getAccessToken();

        $this->assertEquals($accessToken, $token);
    }

    public function testGetAccessTokenThrowsExceptionOnInvalidStatusCode()
    {
        $this->expectException(SpotifyAuthenticatorException::class);
        $this->expectExceptionMessage('Unexpected SpotifyAuthenticator response with Code: 500');

        $response = $this->createMock(ResponseInterface::class);
        $responseBody = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(500);
        $response->method('getBody')->willReturn($responseBody);
        $responseBody->method('__toString')->willReturn('{"error": "Internal Server Error"}');

        $this->client->method('request')->willReturn($response);

        $this->spotifyAuthenticator->getAccessToken();
    }

    public function testGetAccessTokenThrowsExceptionOnMissingAccessTokenInResponse()
    {
        $this->expectException(SpotifyAuthenticatorException::class);
        $this->expectExceptionMessage('Missing access token from Spotify authenticator response');

        $response = $this->createMock(ResponseInterface::class);
        $responseBody = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($responseBody);
        $responseBody->method('__toString')->willReturn('{}');

        $this->client->method('request')->willReturn($response);

        $this->spotifyAuthenticator->getAccessToken();
    }

    public function testGetAccessTokenThrowsExceptionOnBadResponseException()
    {
        $this->expectException(SpotifyAuthenticatorException::class);
        $this->expectExceptionMessage('Something went wrong while retrieving the Spotify bearerToken');

        $badResponseException = $this->createMock(BadResponseException::class);
        $badResponseException->method('getResponse')->willReturn($this->createMock(ResponseInterface::class));

        $this->client->method('request')->willThrowException($badResponseException);
        $this->logger->expects($this->once())->method('error');

        $this->spotifyAuthenticator->getAccessToken();
    }
}
