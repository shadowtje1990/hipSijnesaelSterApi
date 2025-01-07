<?php

namespace App\Playlist\Services;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\StorePlaylistDTO;
use App\Playlist\DTO\UpdatePlaylistDTO;

interface FileStorage
{
    public function store(StorePlaylistDTO $storePlaylistDTO): void;

    public function get(NameIdentifier $nameIdentifier): string;

    public function getAllFiles(): array;

    public function updateFile(NameIdentifier $nameIdentifier, UpdatePlaylistDTO $updatePlaylistDTO);

    public function removeFile(NameIdentifier $nameIdentifier);
}
