<?php

namespace App\TrackFinder\Services;

use App\Domain\TrackCollection;
use App\TrackFinder\Domain\SpotifySearchItem;
use App\TrackFinder\Domain\TrackSearchCollection;

interface TrackFinderServiceInterface
{
    public function search(SpotifySearchItem $trackSearchItem): TrackSearchCollection;
}
