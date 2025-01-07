<?php

namespace Tests\Unit\Playlist\DTO;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\RemovePlaylistDTO;
use PHPUnit\Framework\TestCase;

class RemovePlaylistDTOTest extends TestCase
{
    public function testCreateFromSearchCriteria(): void
    {
        $searchCriteria = ['name' => 'My Playlist'];
        $dto = RemovePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertInstanceOf(RemovePlaylistDTO::class, $dto);
        $this->assertInstanceOf(NameIdentifier::class, $dto->nameIdentifier);
        $this->assertEquals('playlist-My_Playlist', $dto->nameIdentifier->value);
        $this->assertEquals('My Playlist', $dto->originalName);
    }

    public function testCreateFromSearchCriteriaWithSpecialCharacters(): void
    {
        $searchCriteria = ['name' => 'Playlist!@#'];
        $dto = RemovePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-Playlist!@#', $dto->nameIdentifier->value);
        $this->assertEquals('Playlist!@#', $dto->originalName);
    }

    public function testCreateFromSearchCriteriaWithEmptyName(): void
    {
        $searchCriteria = ['name' => ''];
        $dto = RemovePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-', $dto->nameIdentifier->value);
        $this->assertEquals('', $dto->originalName);
    }

    public function testCreateFromSearchCriteriaWithWhitespaceName(): void
    {
        $searchCriteria = ['name' => '   '];
        $dto = RemovePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-___', $dto->nameIdentifier->value);
        $this->assertEquals('   ', $dto->originalName);
    }
}
