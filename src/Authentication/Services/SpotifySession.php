<?php

namespace App\Authentication\Services;

class SpotifySession
{
    private const ACCESS_TOKEN_KEY = 'spotify_access_token';
    private const REFRESH_TOKEN_KEY = 'spotify_refresh_token';
    private const EXPIRATION_TIME_KEY = 'spotify_expiration_time';
    protected string $refreshToken = '';
    protected string $expirationTime = '';
    protected string $accessToken = '';
    protected string $scope = '';

    public function __construct(
        private readonly string $spotifyClientId,
        private readonly string $spotifyClientSecret,
        private readonly string $redirectUri,
        private readonly string $spotifyAccountApi,
        private readonly ?SpotifyRequest $request = null,
    ) {
    }

    public function generateCodeChallenge(string $codeVerifier, string $hashAlgo = 'sha256'): string
    {
        $challenge = hash($hashAlgo, $codeVerifier, true);
        $challenge = base64_encode($challenge);
        $challenge = strtr($challenge, '+/', '-_');

        return rtrim($challenge, '=');
    }

    public function generateCodeVerifier(int $length = 128): string
    {
        return $this->generateState($length);
    }

    public function generateState(int $length = 16): string
    {
        return bin2hex(
            random_bytes($length / 2)
        );
    }

    public function getAuthorizeUrl(array|object $options = []): string
    {
        $options = (array) $options;

        $parameters = [
            'client_id' => $this->spotifyClientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => isset($options['scope']) ? implode(' ', $options['scope']) : null,
            'show_dialog' => !empty($options['show_dialog']) ? 'true' : null,
            'state' => $options['state'] ?? null,
        ];

        // PKCE flows
        if (isset($options['code_challenge'])) {
            $parameters['code_challenge'] = $options['code_challenge'];
            $parameters['code_challenge_method'] = $options['code_challenge_method'] ?? 'S256';
        }

        return $this->spotifyAccountApi.'/authorize?'.http_build_query($parameters, '', '&');
    }

    public function requestAccessToken(string $authorizationCode, string $codeVerifier = ''): bool
    {
        $parameters = [
            'client_id' => $this->spotifyClientId,
            'code' => $authorizationCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
        ];

        // code verifier for PKCE, else client secret
        if ($codeVerifier) {
            $parameters['code_verifier'] = $codeVerifier;
        } else {
            $parameters['client_secret'] = $this->spotifyClientSecret;
        }

        $response = $this->request->send('POST', '/api/token', $parameters, []);
        $responseBody = (string) $response->getBody();
        $data = json_decode($responseBody);

        if (isset($data->refresh_token) && isset($data->access_token)) {
            $this->refreshToken = $data->refresh_token;
            $this->accessToken = $data->access_token;
            $this->expirationTime = time() + $data->expires_in;
            $this->scope = $data->scope ?? $this->scope;

            return true;
        }

        return false;
    }

    public function refreshAccessToken(?string $refreshToken = null): string
    {
        $parameters = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken ?? $this->refreshToken,
        ];

        $headers = [];
        if ($this->spotifyClientSecret) {
            $payload = base64_encode($this->spotifyClientId.':'.$this->spotifyClientSecret);

            $headers = [
                'Authorization' => 'Basic '.$payload,
            ];
        }

        $response = $this->request->send('POST', '/api/token', $parameters, $headers);
        $responseBody = json_decode((string) $response->getBody());

        if (isset($responseBody->access_token)) {
            $this->accessToken = $responseBody->access_token;
            $this->expirationTime = time() + $responseBody->expires_in;
            $this->scope = $responseBody->scope ?? $this->scope;

            if (isset($responseBody->refresh_token)) {
                $this->refreshToken = $responseBody->refresh_token;
            }

            return true;
        }

        return false;
    }

    public function requestCredentialsToken(): bool
    {
        $parameters = [
            'grant_type' => 'client_credentials',
        ];

        $headers = [
            'Authorization' => 'Basic '.base64_encode($this->spotifyClientId.':'.$this->spotifyClientSecret),
        ];

        ['body' => $response] = $this->request->send('POST', '/api/token', $parameters, $headers);

        if (isset($response->access_token)) {
            $this->accessToken = $response->access_token;
            $this->expirationTime = time() + $response->expires_in;
            $this->scope = $response->scope ?? $this->scope;

            return true;
        }

        return false;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenExpiration(): int
    {
        return $this->expirationTime;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getScope(): array
    {
        return explode(' ', $this->scope);
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }
}
