<?php

namespace App\Playlist\Services;

use App\Playlist\Domain\Playlist;
use App\Playlist\DTO\RemovePlaylistDTO;
use App\Playlist\DTO\RetrievePlaylistDTO;
use App\Playlist\DTO\StorePlaylistDTO;
use App\Playlist\DTO\UpdatePlaylistDTO;
use App\Playlist\Exceptions\PlaylistNotFoundException;

class PlaylistStorageService
{
    public function __construct(private readonly PlaylistFileStorageService $playlistFileStorageService)
    {
    }

    public function retrievePlaylist(RetrievePlaylistDTO $retrievePlaylistDTO): Playlist
    {
        try {
            $jsonPlaylistData = $this->playlistFileStorageService->get($retrievePlaylistDTO->nameIdentifier);

            return Playlist::create($jsonPlaylistData);
        } catch (\Exception $exception) {
            throw new PlaylistNotFoundException(sprintf('Playlist not found, error: %s', $exception->getMessage()));
        }
    }

    public function retrievePlaylistNames(): array
    {
        return !empty($this->playlistFileStorageService->getAllFiles())
            ? $this->playlistFileStorageService->getAllFiles()
            : [];
    }

    public function updatePlaylist(UpdatePlaylistDTO $updatePlaylistDTO): void
    {
        $this->playlistFileStorageService->updateFile($updatePlaylistDTO->nameIdentifier, $updatePlaylistDTO);
    }

    public function save(StorePlaylistDTO $storePlaylistDTO): void
    {
        $this->playlistFileStorageService->store($storePlaylistDTO);
    }

    public function remove(RemovePlaylistDTO $removePlaylistDTO): void
    {
        $this->playlistFileStorageService->removeFile($removePlaylistDTO->nameIdentifier);
    }
}
