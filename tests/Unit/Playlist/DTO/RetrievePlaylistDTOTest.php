<?php

namespace Tests\Unit\Playlist\DTO;

use App\Playlist\Domain\NameIdentifier;
use App\Playlist\DTO\RetrievePlaylistDTO;
use PHPUnit\Framework\TestCase;

class RetrievePlaylistDTOTest extends TestCase
{
    public function testCreateFromSearchCriteria(): void
    {
        $searchCriteria = ['name' => 'Chill Playlist'];
        $dto = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertInstanceOf(RetrievePlaylistDTO::class, $dto);
        $this->assertInstanceOf(NameIdentifier::class, $dto->nameIdentifier);
        $this->assertEquals('playlist-Chill_Playlist', $dto->nameIdentifier->value);
        $this->assertEquals('Chill Playlist', $dto->originalName);
    }

    public function testCreateFromSearchCriteriaWithSpecialCharacters(): void
    {
        $searchCriteria = ['name' => 'Dance&Fun'];
        $dto = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-Dance&Fun', $dto->nameIdentifier->value);
        $this->assertEquals('Dance&Fun', $dto->originalName);
    }

    public function testCreateFromSearchCriteriaWithEmptyName(): void
    {
        $searchCriteria = ['name' => ''];
        $dto = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-', $dto->nameIdentifier->value);
        $this->assertEquals('', $dto->originalName);
    }

    public function testCreateFromSearchCriteriaWithWhitespaceName(): void
    {
        $searchCriteria = ['name' => '  Relax  '];
        $dto = RetrievePlaylistDTO::fromSearchCriteria($searchCriteria);

        $this->assertEquals('playlist-__Relax__', $dto->nameIdentifier->value); // Spaces replaced with underscores
        $this->assertEquals('  Relax  ', $dto->originalName);
    }
}
