<?php

namespace App\Authentication\Validators;

use App\Validators\AbstractValidator;

class SpotifyAuthValidator extends AbstractValidator
{
    public function validateRetrieveToken(array $input): void
    {
        $this->isMandatory($input, 'code');
    }

    public function validateRefreshAccessToken(array $input)
    {
        $this->isMandatory($input, 'refreshToken');
    }
}
