<?php

namespace App\Authentication\Services;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// TODO: not used anymore. Keep the class for some references and refactoring.
class SpotifyAuthService implements SpotifyServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $accessToken = '';
    protected array $options = [
        'auto_refresh' => false,
        'auto_retry' => false,
        'return_assoc' => false,
    ];
    protected ?SpotifyRequest $request = null;
    protected ?SpotifySession $session = null;

    public function __construct(
        //        private readonly string $spotifyClientId,
        //        private readonly string $spotifyClientSecret,
        //        private readonly string $redirectUri,
        //        private readonly string $spotifyWebApi,

        private readonly ClientInterface $spotifyAccountClient,
        array|object $options = [],
        ?SpotifySession $session = null,
        ?SpotifyRequest $request = null)
    {
        $this->setOptions($options);
        $this->setSession($session);
        $this->request = $request ?? new SpotifyRequest($this->spotifyAccountClient);
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
