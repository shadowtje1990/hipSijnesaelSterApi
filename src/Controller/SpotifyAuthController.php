<?php

namespace App\Controller;

use App\Authentication\DTO\RefreshSpotifyAccessTokenDTO;
use App\Authentication\DTO\RetrieveSpotifyTokenDTO;
use App\Authentication\Services\SpotifyAuthService;
use App\Authentication\Services\SpotifySession;
use App\Authentication\Validators\SpotifyAuthValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SpotifyAuthController
{
    private const ACCESS_TOKEN_KEY = 'spotify_access_token';
    private const REFRESH_TOKEN_KEY = 'spotify_refresh_token';
    private const EXPIRATION_TIME_KEY = 'spotify_expiration_time';
    protected string $refreshToken = '';
    protected string $expirationTime = '';
    protected string $accessToken = '';

    public function __construct(
        private readonly SpotifyAuthService $spotifyAuthService,
        private readonly SpotifyAuthValidator $validator,
        private readonly SpotifySession $session,
    ) {
    }

    #[Route('/api/login', name: 'get_login', methods: ['GET'])]
    public function login(): Response
    {
        $state = $this->session->generateState();
        $options = [
            'scope' => [
                'streaming',
                'user-read-playback-state',
                'user-modify-playback-state',
                'user-read-currently-playing',
            ],
            'state' => $state,
        ];

        return new JsonResponse(['redirectUrl' => $this->session->getAuthorizeUrl($options)]);

        //        $state = bin2hex(random_bytes(8));
        //        $scope = 'streaming user-read-playback-state user-modify-playback-state user-read-currently-playing';
        //        $redirectUrl = $this->spotifyAuthService->getAuthorizationRedirectUrl($state, $scope);

        //        return new JsonResponse(['redirectUrl' => $redirectUrl]);
    }

    #[Route('/api/token', name: 'retrieve_token', methods: ['POST'])]
    public function token(Request $request): Response
    {
        $searchCriteria = $this->getValidatedInputForRetrieveToken($request);
        $retrieveSpotifyTokenDTO = RetrieveSpotifyTokenDTO::fromSearchCriteria($searchCriteria);
        $this->session->requestAccessToken($retrieveSpotifyTokenDTO->code);
        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();
        $tokenExpirationTime = $this->session->getTokenExpiration();

        $this->storeTokens($request->getSession());

        $array = [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expirationTime' => $tokenExpirationTime,
        ];
        //        $json = $this->spotifyAuthService->retrieveToken($retrieveSpotifyTokenDTO);
        //        $this->playlistStorageService->save($storePlaylistDTO);

        return new Response(json_encode($array), Response::HTTP_OK);
    }

    #[Route('/api/refreshAccessToken', name: 'refresh_access_token', methods: ['POST'])]
    public function refreshAccessToken(Request $request): Response
    {
        $searchCriteria = $this->getValidatedInputForRefreshAccessToken($request);
        $retrieveSpotifyTokenDTO = RefreshSpotifyAccessTokenDTO::fromSearchCriteria($searchCriteria);
        $this->session->refreshAccessToken($retrieveSpotifyTokenDTO->refreshToken);
        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();
        $tokenExpirationTime = $this->session->getTokenExpiration();

        $this->storeTokens($request->getSession());

        $array = [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expirationTime' => $tokenExpirationTime,
        ];
        //        $json = $this->spotifyAuthService->retrieveToken($retrieveSpotifyTokenDTO);
        //        $this->playlistStorageService->save($storePlaylistDTO);

        return new Response(json_encode($array), Response::HTTP_OK);
    }

    #[Route('/api/playTrack', name: 'play_track', methods: ['POST'])]
    public function playTrackOnDevice(Request $request): Response
    {
        $searchCriteria = $this->getValidatedInputForPlayTrackOnDevice($request);
        if ($this->ensureAccessTokenIsValid($request->getSession())) {
            $accessToken = $this->session->getAccessToken();
        } elseif ($request->getSession()->has(self::REFRESH_TOKEN_KEY)) {
            $refreshedToken = $this->session->refreshAccessToken($request->getSession()->has(self::REFRESH_TOKEN_KEY));
            if ($refreshedToken) {
                $this->storeTokens($request->getSession());
            }
            $accessToken = $this->session->getAccessToken();
        } else {
            return new Response('Token is not found', Response::HTTP_NOT_FOUND);
        }

        $this->spotifyAuthService->setAccessToken($accessToken);
        $this->spotifyAuthService->play($searchCriteria['deviceId'], $searchCriteria['trackUri']);
        //        $retrieveSpotifyTokenDTO = RetrieveSpotifyTokenDTO::fromSearchCriteria($searchCriteria);
        //        $this->session->requestAccessToken($retrieveSpotifyTokenDTO->code);
        //        $accessToken = $this->session->getAccessToken();
        //        $refreshToken = $this->session->getRefreshToken();
        //
        //        $array = [
        //            'accessToken' => $accessToken,
        //            'refreshToken' => $refreshToken,
        //        ];
        //        $json = $this->spotifyAuthService->retrieveToken($retrieveSpotifyTokenDTO);
        //        $this->playlistStorageService->save($storePlaylistDTO);

        return new Response(null, Response::HTTP_OK);
    }

    public function storeTokens(SessionInterface $session): void
    {
        $session->set(self::ACCESS_TOKEN_KEY, $this->accessToken);
        $session->set(self::REFRESH_TOKEN_KEY, $this->refreshToken);
        $session->set(self::EXPIRATION_TIME_KEY, $this->expirationTime);
    }

    public function ensureAccessTokenIsValid(SessionInterface $session): bool
    {
        if ($this->isAccessTokenExpired()) {
            $refreshedToken = $this->session->refreshAccessToken();
            $this->storeTokens($session);

            return $refreshedToken;
        }

        return true;
    }

    public function isAccessTokenExpired(): bool
    {
        return time() >= $this->expirationTime;
    }

    public function loadTokens(): void
    {
        $this->accessToken = $this->session->get(self::ACCESS_TOKEN_KEY, '');
        $this->refreshToken = $this->session->get(self::REFRESH_TOKEN_KEY, '');
        $this->expirationTime = $this->session->get(self::EXPIRATION_TIME_KEY, 0);
    }

    private function getValidatedInputForRetrieveToken(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $searchInput = [
            'code' => $data['code'] ?? '',
        ];

        $this->validator->validateRetrieveToken($searchInput);

        return $searchInput;
    }

    private function getValidatedInputForRefreshAccessToken(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $searchInput = [
            'refreshToken' => $data['refreshToken'] ?? '',
        ];

        $this->validator->validateRefreshAccessToken($searchInput);

        return $searchInput;
    }
}
