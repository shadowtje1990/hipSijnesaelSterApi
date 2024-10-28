<?php

namespace App\Validators;

use App\Shared\Exceptions\ValidationException;

class TrackCollectionValidator extends AbstractValidator
{
    /**
     * @throws ValidationException
     */
    public function validateTrackCollection(array $input): void
    {
        if (!empty($input['trackSearchCollection'])) {
            array_map(function($trackCollectionItem) {
                $this->isMandatory($trackCollectionItem, 'artist');
                $this->isString($trackCollectionItem, 'artist');

                $this->isMandatory($trackCollectionItem, 'track');
                $this->isString($trackCollectionItem, 'track');
            }, $input['trackSearchCollection']);
        }
    }
}