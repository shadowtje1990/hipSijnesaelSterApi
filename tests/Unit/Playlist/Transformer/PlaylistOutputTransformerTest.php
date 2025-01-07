<?php

namespace Tests\Unit\Playlist\Transformer;

use App\Playlist\Domain\Playlist;
use App\Playlist\Transformer\PlaylistOutputTransformer;
use PHPUnit\Framework\TestCase;

class PlaylistOutputTransformerTest extends TestCase
{
    public function testTransformPlaylist(): void
    {
        $jsonPlaylistData = json_encode([
            'nameIdentifier' => 'Test',
            'originalName' => 'Test',
            'playlist' => [
                ['id' => 1, 'name' => 'Track 1', 'artist' => 'Artist 1'],
                ['id' => 2, 'name' => 'Track 2', 'artist' => 'Artist 2'],
            ],
        ]);

        $playlist = Playlist::create($jsonPlaylistData);

        $transformer = new PlaylistOutputTransformer();
        $result = $transformer->transformPlaylist($playlist);

        $expectedResult = [
            'fileNameIdentifier' => 'playlist-Test',
            'name' => 'Test',
            'tracks' => [
                ['id' => 1, 'name' => 'Track 1', 'artist' => 'Artist 1'],
                ['id' => 2, 'name' => 'Track 2', 'artist' => 'Artist 2'],
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testTransformEmptyPlaylist(): void
    {
        $playlist = Playlist::empty();

        $transformer = new PlaylistOutputTransformer();
        $result = $transformer->transformPlaylist($playlist);

        $expectedResult = [
            'fileNameIdentifier' => '',
            'name' => '',
            'tracks' => [],
        ];

        $this->assertEquals($expectedResult, $result);
    }
}
