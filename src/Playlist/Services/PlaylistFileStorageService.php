<?php

namespace App\Playlist\Services;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\StorePlaylistDTO;
use App\Playlist\DTO\UpdatePlaylistDTO;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class PlaylistFileStorageService implements FileStorage
{
    private string $dataDirectory;
    private Filesystem $fileSystem;

    public function __construct(string $dataDirectory)
    {
        $this->dataDirectory = $dataDirectory.'/playlist/';
        $this->fileSystem = new Filesystem();

        if (!$this->fileSystem->exists($this->dataDirectory)) {
            $this->fileSystem->mkdir($this->dataDirectory, 0775);
        }
    }

    public function get(NameIdentifier $nameIdentifier): string
    {
        $filePath = $this->dataDirectory.'/'.$nameIdentifier->value;

        if (!$this->fileSystem->exists($filePath)) {
            throw new \RuntimeException("File '{$nameIdentifier->value}' does not exist in {$this->dataDirectory}.");
        }

        try {
            return file_get_contents($filePath);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to read file: '.$e->getMessage());
        }
    }

    public function store(StorePlaylistDTO $storePlaylistDTO): void
    {
        $filePath = $this->dataDirectory.'/'.$storePlaylistDTO->nameIdentifier->value;

        try {
            $this->fileSystem->dumpFile($filePath, $storePlaylistDTO->toJson());
        } catch (IOExceptionInterface $e) {
            throw new \RuntimeException('Failed to write file: '.$e->getMessage());
        }
    }

    public function getAllFiles(): array
    {
        try {
            $files = scandir($this->dataDirectory);

            return array_values(
                array_filter($files, fn ($file) => is_file($this->dataDirectory.'/'.$file))
            );
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to retrieve files: '.$e->getMessage());
        }
    }

    public function updateFile(NameIdentifier $nameIdentifier, UpdatePlaylistDTO $updatePlaylistDTO): void
    {
        $filePath = $this->dataDirectory.'/'.$nameIdentifier->value;

        if (!$this->fileSystem->exists($filePath)) {
            throw new \RuntimeException("File '{$nameIdentifier->value}' does not exist in {$this->dataDirectory}.");
        }

        try {
            $this->fileSystem->dumpFile($filePath, $updatePlaylistDTO->toJson());
        } catch (IOExceptionInterface $e) {
            throw new \RuntimeException('Failed to update file: '.$e->getMessage());
        }
    }

    public function removeFile(NameIdentifier $nameIdentifier): void
    {
        $filePath = $this->dataDirectory.'/'.$nameIdentifier->value;

        if (!$this->fileSystem->exists($filePath)) {
            throw new \RuntimeException("File '{$nameIdentifier->value}' does not exist in {$this->dataDirectory}.");
        }

        try {
            $this->fileSystem->remove($filePath);
        } catch (IOExceptionInterface $e) {
            throw new \RuntimeException('Failed to remove file: '.$e->getMessage());
        }
    }
}
