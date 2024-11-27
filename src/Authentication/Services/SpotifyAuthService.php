<?php

namespace App\Authentication\Services;

use App\Exceptions\SpotifyApiException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SpotifyAuthService implements SpotifyServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $accessToken = '';
    protected array $lastResponse = [];
    protected array $options = [
        'auto_refresh' => false,
        'auto_retry' => false,
        'return_assoc' => false,
    ];
    protected ?SpotifyRequest $request = null;
    protected ?SpotifySession $session = null;

    public function __construct(
        private readonly string $spotifyClientId,
        private readonly string $spotifyClientSecret,
        private readonly string $redirectUri,
        private readonly string $spotifyWebApi,
        private readonly ClientInterface $spotifyAccountClient,
        array|object $options = [],
        ?SpotifySession $session = null,
        ?SpotifyRequest $request = null)
    {
        $this->setOptions($options);
        $this->setSession($session);
        $this->request = $request ?? new SpotifyRequest($this->spotifyAccountClient);
    }

    public function play(string $deviceId, array $trackUris): bool
    {
        if (empty($trackUris)) {
            return false;
        }

        $options = [
            'uris' => $trackUris,
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $uri = sprintf('%s/v1/me/player/play?device_id=%s', $this->spotifyWebApi, $deviceId);

        $response = $this->sendRequest('PUT', $uri, $options, $headers);
        $rawArray = json_decode((string) $response->getBody(), true);

        if (!empty($rawArray['error'])) {
            $result = $this->session->refreshAccessToken();
            var_dump($result);die;
            if (!$result) {
                throw new SpotifyApiException('Could not refresh access token.');
            }

            $response = $this->sendRequest('PUT', $uri, $options, $headers);

            return true;
        }
                return true;
//        return 204 == $this->lastResponse['status'];
    }

    //    public function getAuthorizationRedirectUrl(string $state, string $scope): string
    //    {
    //        return sprintf('https://accounts.spotify.com/authorize?response_type=code&client_id=%s&redirect_uri=%s&scope=%s&state=%s',
    //            $this->spotifyClientId,
    //            $this->redirectUri,
    //            $scope,
    //            $state);
    //    }
    //
    //    public function retrieveToken(RetrieveSpotifyTokenDTO $retrieveSpotifyTokenDTO): string
    //    {
    //        $payload = [
    //            'grant_type' => 'authorization_code',
    //            'code' => $retrieveSpotifyTokenDTO->code,
    //            'redirect_uri' => $this->redirectUri,
    //            'client_id' => $this->spotifyClientId,
    //        ];
    //
    //        $headers = [
    //            'Authorization' => 'Basic '.base64_encode($this->spotifyClientId.':'.$this->spotifyClientSecret),
    //        ];
    //
    //        try {
    //            $request = $this->request('POST', '/api/token', [
    //                'form_params' => $payload,
    //                'headers' => $headers,
    //            ]);
    //        } catch (RequestException $e) {
    //            if ($e->hasResponse()) {
    //                $errorResponse = (string) $e->getResponse()->getBody();
    //                throw new \RuntimeException('Spotify token request failed: '.$errorResponse);
    //            }
    //            throw new \RuntimeException('Spotify token request failed: '.$e->getMessage());
    //        }
    //
    //        return (string) $request->getBody();
    //    }

    //        private function request(string $method, string $uri, array $options = []): ResponseInterface
    //        {
    //            try {
    //                //            $options = $this->appendBearerToken($options);
    //
    //                return $this->spotifyAccountClient->request($method, $uri, $options);
    //            } catch (BadResponseException $exception) {
    //                throw $this->throwExceptionWithParsedMessage($exception);
    //            }
    //        }

    private function throwExceptionWithParsedMessage(BadResponseException $exception): SpotifyApiException
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $responseBodyAsString = (string) $exception->getResponse()->getBody();
        $responseMessage = $exception->getResponse()->getReasonPhrase();

        $message = sprintf(
            'Unexpected Spotify response. Code: %s | Message: %s | Body: %s',
            $statusCode,
            $responseMessage,
            !empty($responseBodyAsString) ? $responseBodyAsString : '?'
        );

        $this->logMessageAsError($message);

        return new SpotifyApiException('Something went wrong while retrieving Spotify Track', $statusCode);
    }

    private function logMessageAsError(string $message): void
    {
        $this->logger->error($message);
    }

    protected function sendRequest(
        string $method,
        string $uri,
        string|array $parameters = [],
        array $headers = [],
    ): ResponseInterface {
        $this->request->setOptions([
            'return_assoc' => $this->options['return_assoc'],
        ]);

        try {
            $headers = $this->authHeaders($headers);

            return $this->request->send($method, $uri, $parameters, $headers);
        } catch (SpotifyApiException $e) {
            if ($this->options['auto_refresh'] && $e->hasExpiredToken($e->getMessage())) {
                $result = $this->session->refreshAccessToken();
                if (!$result) {
                    throw new SpotifyApiException('Could not refresh access token.');
                }

                return $this->sendRequest($method, $uri, $parameters, $headers);
            } elseif ($this->options['auto_retry'] && $e->isRateLimited()) {
                ['headers' => $lastHeaders] = $this->request->getLastResponse();

                sleep((int) $lastHeaders['retry-after']);

                return $this->sendRequest($method, $uri, $parameters, $headers);
            }

            throw $e;
        }
    }

    protected function authHeaders(array $headers = []): array
    {
        $accessToken = $this->session ? $this->session->getAccessToken() : '';

        if ($accessToken) {
            $headers = array_merge($headers, [
                'Authorization' => 'Bearer '.$accessToken,
            ]);
        }

        return $headers;
    }

    public function getRequest(): SpotifyRequest
    {
        return $this->request;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setOptions(array|object $options): self
    {
        $this->options = array_merge($this->options, (array) $options);

        return $this;
    }

    public function setSession(?SpotifySession $session): self
    {
        $this->session = $session;

        return $this;
    }
}
