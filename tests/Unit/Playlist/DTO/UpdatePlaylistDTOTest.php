<?php

namespace Tests\Unit\Playlist\DTO;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\UpdatePlaylistDTO;
use PHPUnit\Framework\TestCase;

class UpdatePlaylistDTOTest extends TestCase
{
    public function testCreateFromSearchCriteria(): void
    {
        $searchCriteria = [
            'name' => 'My Playlist',
            'playlist' => [
                ['track' => 'Song 1', 'artist' => 'Artist 1'],
                ['track' => 'Song 2', 'artist' => 'Artist 2'],
            ],
        ];

        $dto = UpdatePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertInstanceOf(UpdatePlaylistDTO::class, $dto);
        $this->assertInstanceOf(NameIdentifier::class, $dto->nameIdentifier);
        $this->assertEquals('playlist-My_Playlist', $dto->nameIdentifier->value);
        $this->assertEquals('My Playlist', $dto->originalName);
        $this->assertEquals($searchCriteria['playlist'], $dto->playlist);
    }

    public function testCreateFromSearchCriteriaWithEmptyPlaylist(): void
    {
        $searchCriteria = [
            'name' => 'Empty Playlist',
            'playlist' => [],
        ];

        $dto = UpdatePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-Empty_Playlist', $dto->nameIdentifier->value);
        $this->assertEquals('Empty Playlist', $dto->originalName);
        $this->assertEmpty($dto->playlist);
    }

    public function testCreateFromSearchCriteriaWithSpecialCharacters(): void
    {
        $searchCriteria = [
            'name' => 'Workout & Fun',
            'playlist' => [
                ['track' => 'Track A', 'artist' => 'Artist A'],
            ],
        ];

        $dto = UpdatePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-Workout_&_Fun', $dto->nameIdentifier->value);
        $this->assertEquals('Workout & Fun', $dto->originalName);
        $this->assertEquals($searchCriteria['playlist'], $dto->playlist);
    }

    public function testToJson(): void
    {
        $searchCriteria = [
            'name' => 'Chill Vibes',
            'playlist' => [
                ['track' => 'Relax Track', 'artist' => 'Relax Artist'],
            ],
        ];

        $dto = UpdatePlaylistDTO::fromSearchCriteria($searchCriteria);
        $json = $dto->toJson();

        $expectedJson = json_encode([
            'nameIdentifier' => 'playlist-Chill_Vibes',
            'originalName' => 'Chill Vibes',
            'playlist' => $searchCriteria['playlist'],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
