<?php

namespace App\Authentication\Services;

use App\Authentication\Exceptions\SpotifyApiException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SpotifyRequest implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected array $lastResponse = [];
    protected array $options = [
        'curl_options' => [],
        'return_assoc' => false,
    ];

    public function __construct(private readonly ClientInterface $spotifyAccountClient, array|object $options = [])
    {
        $this->setOptions($options);
    }

    public function send(string $method, string $uri, string|array $parameters = [], array $headers = []): ResponseInterface
    {
        $this->lastResponse = [];

        $method = strtoupper($method);

        $options = [
            'headers' => $headers,
            'form_params' => $parameters,
        ];

        if (!empty($headers) && !empty($headers['Content-Type']) && !empty('application/json' === $headers['Content-Type'])) {
            $options = [
                'headers' => $headers,
                'body' => json_encode($parameters),
            ];
        }

        if ('GET' === $method) {
            $options['query'] = $parameters;
        }

        try {
            return $this->spotifyAccountClient->request($method, $uri, $options);
        } catch (BadResponseException $exception) {
            throw $this->throwExceptionWithParsedMessage($exception);
        }
    }

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

        return new SpotifyApiException('Something went terrible wrong while calling the spotify API', $statusCode);
    }

    public function setOptions(array|object $options): self
    {
        $this->options = array_merge($this->options, (array) $options);

        return $this;
    }

    public function getLastResponse(): array
    {
        return $this->lastResponse;
    }

    private function logMessageAsError(string $message): void
    {
        $this->logger->error($message);
    }
}
