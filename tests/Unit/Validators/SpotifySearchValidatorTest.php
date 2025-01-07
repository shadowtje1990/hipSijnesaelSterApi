<?php

namespace Tests\Unit\Validators;

use App\TrackFinder\Validators\SpotifySearchValidator;
use App\Shared\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class SpotifySearchValidatorTest extends TestCase
{
    private SpotifySearchValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new SpotifySearchValidator();
    }

    public function testValidateSearchValidInput(): void
    {
        $validInput = [
            'artist' => 'Artist Name',
            'track' => 'Track Name',
        ];

        $this->validator->validateSearch($validInput);
        $this->assertTrue(true);
    }

    public function testValidateSearchMissingArtist(): void
    {
        $invalidInput = [
            'track' => 'Track Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field artist is mandatory.');

        $this->validator->validateSearch($invalidInput);
    }

    public function testValidateSearchMissingTrack(): void
    {
        $invalidInput = [
            'artist' => 'Artist Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field track is mandatory.');

        $this->validator->validateSearch($invalidInput);
    }

    public function testValidateSearchInvalidArtistType(): void
    {
        $invalidInput = [
            'artist' => 12345, // Invalid type, should be a string
            'track' => 'Track Name',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field artist must be a string.');

        $this->validator->validateSearch($invalidInput);
    }

    public function testValidateSearchInvalidTrackType(): void
    {
        $invalidInput = [
            'artist' => 'Artist Name',
            'track' => 12345, // Invalid type, should be a string
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field track must be a string.');

        $this->validator->validateSearch($invalidInput);
    }
}
