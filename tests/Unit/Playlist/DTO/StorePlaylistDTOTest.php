<?php

namespace Tests\Unit\Playlist\DTO;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\StorePlaylistDTO;
use PHPUnit\Framework\TestCase;

class StorePlaylistDTOTest extends TestCase
{
    public function testCreateFromSearchCriteria(): void
    {
        $searchCriteria = [
            'name' => 'Chill Playlist',
            'playlist' => [
                ['track' => 'Track 1', 'artist' => 'Artist 1'],
                ['track' => 'Track 2', 'artist' => 'Artist 2'],
            ],
        ];

        $dto = StorePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertInstanceOf(StorePlaylistDTO::class, $dto);
        $this->assertInstanceOf(NameIdentifier::class, $dto->nameIdentifier);
        $this->assertEquals('playlist-Chill_Playlist', $dto->nameIdentifier->value);
        $this->assertEquals('Chill Playlist', $dto->originalName);
        $this->assertEquals($searchCriteria['playlist'], $dto->playlist);
    }

    public function testCreateFromSearchCriteriaWithEmptyPlaylist(): void
    {
        $searchCriteria = [
            'name' => 'Empty Playlist',
            'playlist' => [],
        ];

        $dto = StorePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-Empty_Playlist', $dto->nameIdentifier->value);
        $this->assertEquals('Empty Playlist', $dto->originalName);
        $this->assertEmpty($dto->playlist);
    }

    public function testCreateFromSearchCriteriaWithSpecialCharacters(): void
    {
        $searchCriteria = [
            'name' => 'chillPlaylist',
            'playlist' => [
                ['track' => 'Track 1', 'artist' => 'Artist 1'],
            ],
        ];

        $dto = StorePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-chillPlaylist', $dto->nameIdentifier->value);
        $this->assertEquals('chillPlaylist', $dto->originalName);
        $this->assertEquals($searchCriteria['playlist'], $dto->playlist);
    }

    public function testToJson(): void
    {
        $searchCriteria = [
            'name' => 'Relax Playlist',
            'playlist' => [
                ['track' => 'Track A', 'artist' => 'Artist A'],
            ],
        ];

        $dto = StorePlaylistDTO::fromSearchCriteria($searchCriteria);
        $json = $dto->toJson();

        $expectedJson = json_encode([
            'nameIdentifier' => 'playlist-Relax_Playlist',
            'originalName' => 'Relax Playlist',
            'playlist' => $searchCriteria['playlist'],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
