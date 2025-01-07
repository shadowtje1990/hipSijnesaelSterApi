<?php

namespace Tests\Unit\Playlist\Services;

use App\Playlist\Domain\Playlist;
use App\Playlist\DTO\RemovePlaylistDTO;
use App\Playlist\DTO\RetrievePlaylistDTO;
use App\Playlist\DTO\StorePlaylistDTO;
use App\Playlist\DTO\UpdatePlaylistDTO;
use App\Playlist\Exceptions\PlaylistNotFoundException;
use App\Playlist\Services\PlaylistFileStorageService;
use App\Playlist\Services\PlaylistStorageService;
use PHPUnit\Framework\TestCase;

class PlaylistStorageServiceTest extends TestCase
{
    private PlaylistFileStorageService $playlistFileStorageService;
    private PlaylistStorageService $playlistStorageService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playlistFileStorageService = $this->createMock(PlaylistFileStorageService::class);
        $this->playlistStorageService = new PlaylistStorageService($this->playlistFileStorageService);
    }

    public function testRetrievePlaylistSuccess(): void
    {
        $jsonPlaylistData = json_encode([
            'nameIdentifier' => 'test',
            'originalName' => 'test',
            'playlist' => [
                ['id' => 'track1', 'name' => 'Track 1', 'artist' => 'Artist 1'],
                ['id' => 'track2', 'name' => 'Track 2', 'artist' => 'Artist 2'],
            ],
        ]);

        $nameIdentifier = 'playlist-Test_Playlist';

        $this->playlistFileStorageService
            ->expects($this->once())
            ->method('get')
            ->with($nameIdentifier)
            ->willReturn($jsonPlaylistData);

        $searchCriteria = ['name' => 'Test Playlist'];
        $retrievePlaylistDTO = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);
        $playlist = $this->playlistStorageService->retrievePlaylist($retrievePlaylistDTO);

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertEquals('test', $playlist->name);
    }

    public function testRetrievePlaylistNotFound(): void
    {
        $this->playlistFileStorageService
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('File not found'));

        $searchCriteria = ['name' => 'Nonexistent Playlist'];
        $retrievePlaylistDTO = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->expectException(PlaylistNotFoundException::class);
        $this->expectExceptionMessage('Playlist not found, error: File not found');

        $this->playlistStorageService->retrievePlaylist($retrievePlaylistDTO);
    }

    public function testRetrievePlaylistNames(): void
    {
        $this->playlistFileStorageService
            ->method('getAllFiles')
            ->willReturn(['playlist1.json', 'playlist2.json']);

        $names = $this->playlistStorageService->retrievePlaylistNames();

        $this->assertCount(2, $names);
        $this->assertEquals(['playlist1.json', 'playlist2.json'], $names);
    }

    public function testRetrievePlaylistNamesEmpty(): void
    {
        $this->playlistFileStorageService
            ->expects($this->once())
            ->method('getAllFiles')
            ->willReturn([]);

        $names = $this->playlistStorageService->retrievePlaylistNames();

        $this->assertEmpty($names);
    }

    public function testUpdatePlaylist(): void
    {
        $searchCriteria = ['name' => 'Test Playlist', 'playlist' => ['track' => 'Song 1', 'artist' => 'Artist 1']];
        $updatePlaylistDTO = UpdatePlaylistDTO::fromSearchCriteria($searchCriteria);
        $nameIdentifier = 'playlist-Test_Playlist';

        $this->playlistFileStorageService
            ->expects($this->once())
            ->method('updateFile')
            ->with($nameIdentifier, $updatePlaylistDTO);

        $this->playlistStorageService->updatePlaylist($updatePlaylistDTO);
    }

    public function testSavePlaylist(): void
    {
        $searchCriteria = ['name' => 'Test Playlist', 'playlist' => ['track' => 'Song 1', 'artist' => 'Artist 1']];
        $storePlaylistDTO = StorePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->playlistFileStorageService
            ->expects($this->once())
            ->method('store')
            ->with($storePlaylistDTO);

        $this->playlistStorageService->save($storePlaylistDTO);
    }

    public function testRemovePlaylist(): void
    {
        $searchCriteria = ['name' => 'Test Playlist', 'playlist' => ['track' => 'Song 1', 'artist' => 'Artist 1']];
        $removePlaylistDTO = RemovePlaylistDTO::fromSearchCriteria($searchCriteria);
        $this->playlistFileStorageService
            ->expects($this->once())
            ->method('removeFile')
            ->with('playlist-Test_Playlist');

        $this->playlistStorageService->remove($removePlaylistDTO);
    }
}
