<?php

namespace App\TrackFinder\Validators;

use App\Shared\Exceptions\ValidationException;
use App\Validators\AbstractValidator;

class SpotifySearchValidator extends AbstractValidator
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
}
