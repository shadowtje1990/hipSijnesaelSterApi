<?php

namespace App\Utils;

use App\Exceptions\SpotifyAuthenticatorException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SpotifyAuthenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ClientInterface $spotifyAccountClient,
        private readonly string $spotifyClientId,
        private readonly string $spotifyClientSecret
    ) {}

    public function getAccessToken(): string
    {
        $response = $this->request('POST', 'https://accounts.spotify.com/api/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->spotifyClientId . ':' . $this->spotifyClientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new SpotifyAuthenticatorException(
                sprintf(
                    "Unexpected SpotifyAuthenticator response with Code: %s and Body: %s",
                    $response->getStatusCode(),
                    (string) $response->getBody()
                )
            );
        }

        $json = (string) $response->getBody();
        $data = json_decode($json, true);

        if(!isset($data['access_token'])) {
            throw new SpotifyAuthenticatorException('Missing access token from Spotify authenticator response');
        }

        return $data['access_token'];
    }

    private function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        try {
            return $this->spotifyAccountClient->request($method, $uri, $options,);
        } catch (BadResponseException $exception) {
            throw $this->throwExceptionWithParsedMessage($exception);
        }
    }

    private function throwExceptionWithParsedMessage(BadResponseException $exception): SpotifyAuthenticatorException
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $responseBodyAsString = (string) $exception->getResponse()->getBody();
        $responseMessage = $exception->getResponse()->getReasonPhrase();

        $message = sprintf(
            "Unexpected Spotify authentication response. Code: %s | Message: %s | Body: %s",
            $statusCode,
            $responseMessage,
            !empty($responseBodyAsString) ? $responseBodyAsString : '?'
        );

        $this->logMessageAsError($message);

        return new SpotifyAuthenticatorException('Something went wrong while retrieving the Spotify bearerToken', $statusCode);
    }

    private function logMessageAsError(string $message): void {
        $this->logger->error($message);
    }
}
