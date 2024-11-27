<?php

declare(strict_types=1);

namespace App\Controller;

use App\Playlist\DTO\RemovePlaylistDTO;
use App\Playlist\DTO\RetrievePlaylistDTO;
use App\Playlist\DTO\StorePlaylistDTO;
use App\Playlist\DTO\UpdatePlaylistDTO;
use App\Playlist\Exceptions\PlaylistNotFoundException;
use App\Playlist\Services\PlaylistStorageService;
use App\Playlist\Transformer\PlaylistOutputTransformer;
use App\Playlist\Validators\PlaylistValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaylistApiController
{
    public function __construct(
        private readonly PlaylistValidator $validator,
        private readonly PlaylistStorageService $playlistStorageService,
        private readonly PlaylistOutputTransformer $playlistOutputTransformer,
    ) {
    }

    #[Route('/api/playlist', name: 'get_playlist', methods: ['GET'])]
    public function getPlaylist(Request $request): JsonResponse
    {
        $searchCriteria = $this->getValidatedInputForRetrievingPlaylist($request);
        $retrievePlaylistDTO = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);
        try {
            $playlist = $this->playlistStorageService->retrievePlaylist($retrievePlaylistDTO);
        } catch (PlaylistNotFoundException $exception) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $trackCollectionOutput = $this->playlistOutputTransformer->transformPlaylist($playlist);

        return new JsonResponse($trackCollectionOutput);
    }

    #[Route('/api/playlistNames', name: 'get_playlistNames', methods: ['GET'])]
    public function getPlaylistNames(): JsonResponse
    {
        $playListNames = $this->playlistStorageService->retrievePlaylistNames();
        return new JsonResponse($playListNames);
    }

    #[Route('/api/playlist', name: 'update_playlist', methods: ['PUT'])]
    public function updatePlaylist(Request $request): JsonResponse
    {
        $searchCriteria = $this->getValidatedInputForUpdatePlaylist($request);
        $updatePlaylistDTO = UpdatePlaylistDTO::fromSearchCriteria($searchCriteria);
        $this->playlistStorageService->updatePlaylist($updatePlaylistDTO);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/playlist', name: 'store_playlist', methods: ['POST'])]
    public function storePlaylist(Request $request): JsonResponse
    {
        $searchCriteria = $this->getValidatedInputForStorePlaylist($request);
        $storePlaylistDTO = StorePlaylistDTO::fromSearchCriteria($searchCriteria);
        $this->playlistStorageService->save($storePlaylistDTO);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    #[Route('/api/playlist', name: 'remove_playlist', methods: ['DELETE'])]
    public function removePlaylist(Request $request): JsonResponse
    {
        $searchCriteria = $this->getValidatedInputForRemovingPlaylist($request);
        $removePlaylistDTO = RemovePlaylistDTO::fromSearchCriteria($searchCriteria);
        $this->playlistStorageService->remove($removePlaylistDTO);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getValidatedInputForRetrievingPlaylist(Request $request)
    {
        $searchInput = [
            'name' => $request->get('name', null),
        ];

        $this->validator->validateRetrievePlaylist($searchInput);

        return $searchInput;
    }

    private function getValidatedInputForRemovingPlaylist(Request $request)
    {
        $searchInput = [
            'name' => $request->get('name', null),
        ];

        $this->validator->validateRetrievePlaylist($searchInput);

        return $searchInput;
    }

    private function getValidatedInputForStorePlaylist(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $searchInput = [
            'name' => $data['name'] ?? '',
            'playlist' => $data['playlist'] ?? [],
        ];

        $this->validator->validateStorePlaylist($searchInput);

        return $searchInput;
    }

    private function getValidatedInputForUpdatePlaylist(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $searchInput = [
            'name' => $data['name'] ?? '',
            'playlist' => $data['playlist'] ?? [],
        ];

        $this->validator->validateStorePlaylist($searchInput);

        return $searchInput;
    }
}
