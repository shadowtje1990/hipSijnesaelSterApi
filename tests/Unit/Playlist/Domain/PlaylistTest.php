<?php

namespace Tests\Unit\Playlist\Domain;

use App\Playlist\Domain\Playlist;
use App\Playlist\Domain\NameIdentifier;
use PHPUnit\Framework\TestCase;

class PlaylistTest extends TestCase
{
    public function testCreateFromValidJson(): void
    {
        $jsonPlaylistData = json_encode([
            'nameIdentifier' => 'test',
            'originalName' => 'test',
            'playlist' => [
                ['id' => 'track1', 'name' => 'Track 1', 'artist' => 'Artist 1'],
                ['id' => 'track2', 'name' => 'Track 2', 'artist' => 'Artist 2'],
            ],
        ]);

        $playlist = Playlist::create($jsonPlaylistData);

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertInstanceOf(NameIdentifier::class, $playlist->fileNameIdentifier);
        $this->assertEquals('playlist-test', (string) $playlist->fileNameIdentifier);
        $this->assertEquals('test', $playlist->name);
        $this->assertCount(2, $playlist->tracks);
        $this->assertEquals('Track 1', $playlist->tracks[0]['name']);
        $this->assertEquals('Artist 2', $playlist->tracks[1]['artist']);
    }

    public function testCreateFromInvalidJson(): void
    {
        $this->expectException(\TypeError::class);

        $invalidJsonPlaylistData = json_encode([
            'invalidField' => 'value',
        ]);

        Playlist::create($invalidJsonPlaylistData);
    }

    public function testEmptyPlaylist(): void
    {
        $playlist = Playlist::empty();

        $this->assertInstanceOf(Playlist::class, $playlist);
        $this->assertInstanceOf(NameIdentifier::class, $playlist->fileNameIdentifier);
        $this->assertTrue($playlist->fileNameIdentifier->isEmpty());
        $this->assertEquals('', $playlist->name);
        $this->assertEmpty($playlist->tracks);
    }

    public function testEmptyPlaylistTracks(): void
    {
        $playlist = Playlist::empty();

        $this->assertIsArray($playlist->tracks);
        $this->assertEmpty($playlist->tracks);
    }
}
