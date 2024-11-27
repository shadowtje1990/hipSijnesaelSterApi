<?php

namespace App\TrackFinder\Domain;

class Metadata
{
    private function __construct(
        public readonly int $limit,
        public readonly int $offset,
        public readonly int $total,
        public readonly ?string $current,
        public readonly ?string $next,
        public readonly ?string $previous,
    ) {
    }

    public static function fromArray(array $metadata): self
    {
        return new self(
            $metadata['tracks']['limit'] ?? 0,
            $metadata['tracks']['offset'] ?? 0,
            $metadata['tracks']['total'] ?? 0,
            !empty($metadata['tracks']['href']) ? $metadata['tracks']['href'] : '',
            !empty($metadata['tracks']['next']) ? $metadata['tracks']['next'] : '',
            !empty($metadata['tracks']['previous']) ? $metadata['tracks']['previous'] : '',
        );
    }

    public static function empty(): self
    {
        return new self(0, 0, 0, null, null, null);
    }

    public function toArray(): array
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
            'total' => $this->total,
            'current' => $this->current,
            'next' => $this->next,
            'previous' => $this->previous,
        ];
    }
}
