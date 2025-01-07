<?php

namespace Tests\Unit\Playlist\Services;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\StorePlaylistDTO;
use App\Playlist\DTO\UpdatePlaylistDTO;
use App\Playlist\Services\PlaylistFileStorageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class PlaylistFileStorageServiceTest extends TestCase
{
    private string $testDataDirectory;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->testDataDirectory = sys_get_temp_dir().'/playlist_tests';
        $this->filesystem = new Filesystem();

        if (!$this->filesystem->exists($this->testDataDirectory)) {
            $this->filesystem->mkdir($this->testDataDirectory, 0775);
        }
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->testDataDirectory)) {
            $this->filesystem->remove($this->testDataDirectory);
        }
    }

    public function testStorePlaylist(): void
    {
        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $dto = StorePlaylistDTO::fromSearchCriteria([
            'name' => 'My Playlist',
            'playlist' => ['track' => 'Song 1', 'artist' => 'Artist 1'],
        ]);

        $service->store($dto);

        $filePath = $this->testDataDirectory.'/playlist/playlist-My_Playlist';
        $this->assertFileExists($filePath);

        $fileContents = file_get_contents($filePath);
        $expectedContents = $dto->toJson();
        $this->assertJsonStringEqualsJsonString($expectedContents, $fileContents);
    }

    public function testGetPlaylist(): void
    {
        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $filePath = $this->testDataDirectory.'/playlist/playlist-My_Playlist';
        $this->filesystem->dumpFile($filePath, '{"nameIdentifier":"playlist-My_Playlist","originalName":"My Playlist","playlist":[]}');

        $nameIdentifier = NameIdentifier::fromString('My Playlist');
        $contents = $service->get($nameIdentifier);

        $expectedContents = '{"nameIdentifier":"playlist-My_Playlist","originalName":"My Playlist","playlist":[]}';
        $this->assertJsonStringEqualsJsonString($expectedContents, $contents);
    }

    public function testGetNonExistentFileThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File 'playlist-Non_Existent' does not exist");

        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $nameIdentifier = NameIdentifier::fromString('Non Existent');
        $service->get($nameIdentifier);
    }

    public function testGetAllFiles(): void
    {
        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $this->filesystem->dumpFile($this->testDataDirectory.'/playlist/file1', 'contents1');
        $this->filesystem->dumpFile($this->testDataDirectory.'/playlist/file2', 'contents2');

        $files = $service->getAllFiles();

        $this->assertCount(2, $files);
        $this->assertContains('file1', $files);
        $this->assertContains('file2', $files);
    }

    public function testUpdateFile(): void
    {
        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $filePath = $this->testDataDirectory.'/playlist/playlist-My_Playlist';
        $this->filesystem->dumpFile($filePath, '{"nameIdentifier":"playlist-My_Playlist","originalName":"My Playlist","playlist":[]}');

        $dto = UpdatePlaylistDTO::fromSearchCriteria([
            'name' => 'My Playlist',
            'playlist' => ['track' => 'Song 1', 'artist' => 'Artist 1'],
        ]);

        $service->updateFile($dto->nameIdentifier, $dto);

        $updatedContents = file_get_contents($filePath);
        $expectedContents = $dto->toJson();
        $this->assertJsonStringEqualsJsonString($expectedContents, $updatedContents);
    }

    public function testRemoveFile(): void
    {
        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $filePath = $this->testDataDirectory.'/playlist/playlist-My_Playlist';
        $this->filesystem->dumpFile($filePath, '{"nameIdentifier":"playlist-My_Playlist","originalName":"My Playlist","playlist":[]}');

        $nameIdentifier = NameIdentifier::fromString('My Playlist');
        $service->removeFile($nameIdentifier);

        $this->assertFileDoesNotExist($filePath);
    }

    public function testRemoveNonExistentFileThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File 'playlist-Non_Existent' does not exist");

        $service = new PlaylistFileStorageService($this->testDataDirectory);

        $nameIdentifier = NameIdentifier::fromString('Non Existent');
        $service->removeFile($nameIdentifier);
    }
}
