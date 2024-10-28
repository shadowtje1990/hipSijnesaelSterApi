<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\TrackSearchCollection;
use App\Services\TrackFinderServiceInterface;
use App\Transformer\TrackCollectionOutputTransformer;
use App\Validators\TrackCollectionValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    public function __construct(
        private readonly TrackFinderServiceInterface $trackFinderService,
        private readonly TrackCollectionValidator $validator,
        private readonly TrackCollectionOutputTransformer $collectionOutputTransformer
    ) {}

    #[Route('/api/tracks', name: 'get_track_urls', methods: ['POST'])]
    public function getTrackCollectionFromRequestedTracks(Request $request): Response
    {
        $searchCriteria = $this->getValidatedInputForTrackCollection($request);
        $trackSearchCollection = TrackSearchCollection::fromArray($searchCriteria);
        $trackCollection = $this->trackFinderService->getTrackCollectionFromTrackSearchCollection($trackSearchCollection);
        $trackCollectionOutput = $this->collectionOutputTransformer->transformTrackCollection($trackCollection);

        return new Response(json_encode($trackCollectionOutput));
    }

    private function getValidatedInputForTrackCollection(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        $searchInput = [
         'trackSearchCollection' => $data['trackSearchCollection'] ?? []
        ];

        $this->validator->validateTrackCollection($searchInput);

        return $searchInput;
    }
}