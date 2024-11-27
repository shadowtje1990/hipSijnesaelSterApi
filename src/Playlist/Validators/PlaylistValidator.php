<?php

namespace App\Playlist\Validators;

use App\Shared\Exceptions\ValidationException;
use App\Validators\AbstractValidator;

class PlaylistValidator extends AbstractValidator
{
    /**
     * @throws ValidationException
     */
    public function validateSearch(array $input): void
    {
        $this->isMandatory($input, 'artist');
        $this->isString($input, 'artist');

        $this->isMandatory($input, 'track');
        $this->isString($input, 'track');
    }

    public function validateRetrievePlaylist(array $input): void
    {
        $this->isMandatory($input, 'name');
    }

    public function validateStorePlaylist(array $input): void
    {
        $this->isMandatory($input, 'name');
        $this->isMandatory($input, 'playlist');
    }
}
