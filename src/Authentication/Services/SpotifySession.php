<?php

namespace App\Authentication\Services;

class SpotifySession
{
    protected string $refreshToken = '';
    protected int $expirationTime;
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

        $queryFromHttpBuildQuery = http_build_query($parameters, '', '&');
        $queryString = str_replace('+', '%20', $queryFromHttpBuildQuery);

        return $this->spotifyAccountApi.'/authorize?'.$queryString;
    }

    public function requestAccessToken(string $authorizationCode): bool
    {
        $parameters = [
            'client_id' => $this->spotifyClientId,
            'code' => $authorizationCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'client_secret' => $this->spotifyClientSecret,
        ];

        $response = $this->request->send('POST', '/api/token', $parameters, []);
        $responseBody = (string) $response->getBody();
        $data = json_decode($responseBody);

        if (isset($data->refresh_token) && isset($data->access_token)) {
            $this->refreshToken = $data->refresh_token;
            $this->accessToken = $data->access_token;

            if (!empty($data->expires_in) && is_int($data->expires_in)) {
                $this->expirationTime = time() + $data->expires_in;
            }

            $this->scope = $data->scope ?? $this->scope;

            return true;
        }

        return false;
    }
    // TODO: implement refreshAccesstoken
    //    public function refreshAccessToken(?string $refreshToken = null): string
    //    {
    //        $parameters = [
    //            'grant_type' => 'refresh_token',
    //            'refresh_token' => $refreshToken ?? $this->refreshToken,
    //        ];
    //
    //        $headers = [];
    //        if ($this->spotifyClientSecret) {
    //            $payload = base64_encode($this->spotifyClientId.':'.$this->spotifyClientSecret);
    //
    //            $headers = [
    //                'Authorization' => 'Basic '.$payload,
    //            ];
    //        }
    //
    //        $response = $this->request->send('POST', '/api/token', $parameters, $headers);
    //        $responseBody = json_decode((string) $response->getBody());
    //
    //        if (isset($responseBody->access_token)) {
    //            $this->accessToken = $responseBody->access_token;
    //            $this->expirationTime = time() + $responseBody->expires_in;
    //            $this->scope = $responseBody->scope ?? $this->scope;
    //
    //            if (isset($responseBody->refresh_token)) {
    //                $this->refreshToken = $responseBody->refresh_token;
    //            }
    //
    //            return true;
    //        }
    //
    //        return false;
    //    }

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
}
