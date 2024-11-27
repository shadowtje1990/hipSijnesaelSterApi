<?php

namespace App\Playlist\Transformer;

use App\Playlist\Domain\Playlist;

class PlaylistOutputTransformer
{
    public function transformPlaylist(Playlist $playlist): array
    {
        return [
            'fileNameIdentifier' => $playlist->fileNameIdentifier->value,
            'name' => $playlist->name,
            'tracks' => $playlist->tracks,
        ];
    }
}
