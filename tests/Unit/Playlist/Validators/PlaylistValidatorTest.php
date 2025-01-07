<?php

namespace Tests\Unit\Playlist\Validators;

use App\Playlist\Validators\PlaylistValidator;
use App\Shared\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class PlaylistValidatorTest extends TestCase
{
    private PlaylistValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PlaylistValidator();
    }

    public function testValidSearch(): void
    {
        $input = [
            'artist' => 'Artist Name',
            'track' => 'Track Name',
        ];

        // No exception should be thrown
        $this->validator->validateSearch($input);
        $this->assertTrue(true);
    }

    public function testMissingArtistInSearch(): void
    {
        $input = [
            'track' => 'Track Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field artist is mandatory.');

        $this->validator->validateSearch($input);
    }

    public function testInvalidArtistTypeInSearch(): void
    {
        $input = [
            'artist' => 123,
            'track' => 'Track Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field artist must be a string.');

        $this->validator->validateSearch($input);
    }

    public function testMissingTrackInSearch(): void
    {
        $input = [
            'artist' => 'Artist Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field track is mandatory.');

        $this->validator->validateSearch($input);
    }

    public function testInvalidTrackTypeInSearch(): void
    {
        $input = [
            'artist' => 'Artist Name',
            'track' => 123,
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field track must be a string.');

        $this->validator->validateSearch($input);
    }

    public function testValidRetrievePlaylist(): void
    {
        $input = [
            'name' => 'Playlist Name',
        ];

        $this->validator->validateRetrievePlaylist($input);
        $this->assertTrue(true);
    }

    public function testMissingNameInRetrievePlaylist(): void
    {
        $input = [];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field name is mandatory.');

        $this->validator->validateRetrievePlaylist($input);
    }

    public function testValidStorePlaylist(): void
    {
        $input = [
            'name' => 'Playlist Name',
            'playlist' => ['track1', 'track2'],
        ];

        $this->validator->validateStorePlaylist($input);
        $this->assertTrue(true);
    }

    public function testMissingNameInStorePlaylist(): void
    {
        $input = [
            'playlist' => ['track1', 'track2'],
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field name is mandatory.');

        $this->validator->validateStorePlaylist($input);
    }

    public function testMissingPlaylistInStorePlaylist(): void
    {
        $input = [
            'name' => 'Playlist Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field playlist is mandatory.');

        $this->validator->validateStorePlaylist($input);
    }
}
