<?php

namespace App\Services;

use App\Domain\TrackCollection;
use App\Domain\TrackSearchCollection;

interface TrackFinderServiceInterface
{
    public function getTrackCollectionFromTrackSearchCollection(TrackSearchCollection $trackSearchCollection): TrackCollection;
}