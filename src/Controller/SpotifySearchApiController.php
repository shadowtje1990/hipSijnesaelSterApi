<?php

namespace App\Controller;

use App\TrackFinder\Domain\SpotifySearchItem;
use App\TrackFinder\Services\TrackFinderServiceInterface;
use App\TrackFinder\Transformer\TrackSearchCollectionOutputTransformer;
use App\TrackFinder\Validators\SpotifySearchValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SpotifySearchApiController
{
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_OFFSET = 0;

    public function __construct(
        private readonly TrackFinderServiceInterface $trackFinderService,
        private readonly SpotifySearchValidator $validator,
        private readonly TrackSearchCollectionOutputTransformer $collectionSearchOutputTransformer,
    ) {
    }

    #[Route('/api/search', name: 'get_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $searchCriteria = $this->getValidatedInputForSpotifySearch($request);
        $trackSearchItem = SpotifySearchItem::fromSearchCriteria($searchCriteria);
        $trackSearchCollection = $this->trackFinderService->search($trackSearchItem);
        $trackSearchCollectionOutput = $this->collectionSearchOutputTransformer->transformTrackSearchCollection($trackSearchCollection);

        return new JsonResponse($trackSearchCollectionOutput);
    }

    private function getValidatedInputForSpotifySearch(Request $request)
    {
        $searchInput = [
            'limit' => (int) $request->get('limit', self::DEFAULT_LIMIT),
            'offset' => (int) $request->get('offset', self::DEFAULT_OFFSET),
            'track' => $request->get('track', null),
            'artist' => $request->get('artist', null),
        ];

        $this->validator->validateSearch($searchInput);

        return $searchInput;
    }
}
