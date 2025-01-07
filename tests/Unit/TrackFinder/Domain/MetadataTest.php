<?php

namespace Tests\Unit\TrackFinder\Domain;

use App\TrackFinder\Domain\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function testFromArrayCreatesMetadataFromArray(): void
    {
        $input = [
            'tracks' => [
                'limit' => 20,
                'offset' => 10,
                'total' => 100,
                'href' => 'http://doesNotMatter.com/current',
                'next' => 'http://doesNotMatter.com/next',
                'previous' => 'http://doesNotMatter.com/previous',
            ],
        ];

        $metadata = Metadata::fromArray($input);

        $this->assertSame(20, $metadata->limit);
        $this->assertSame(10, $metadata->offset);
        $this->assertSame(100, $metadata->total);
        $this->assertSame('http://doesNotMatter.com/current', $metadata->current);
        $this->assertSame('http://doesNotMatter.com/next', $metadata->next);
        $this->assertSame('http://doesNotMatter.com/previous', $metadata->previous);
    }

    public function testFromArrayHandlesMissingKeys(): void
    {
        $input = [
            'tracks' => [],
        ];

        $metadata = Metadata::fromArray($input);

        $this->assertSame(0, $metadata->limit);
        $this->assertSame(0, $metadata->offset);
        $this->assertSame(0, $metadata->total);
        $this->assertSame('', $metadata->current);
        $this->assertSame('', $metadata->next);
        $this->assertSame('', $metadata->previous);
    }

    public function testEmptyReturnsEmptyMetadata(): void
    {
        $metadata = Metadata::empty();

        $this->assertSame(0, $metadata->limit);
        $this->assertSame(0, $metadata->offset);
        $this->assertSame(0, $metadata->total);
        $this->assertNull($metadata->current);
        $this->assertNull($metadata->next);
        $this->assertNull($metadata->previous);
    }

    public function testToArrayConvertsMetadataToArray(): void
    {
        $metadata = Metadata::fromArray([
            'tracks' => [
                'limit' => 20,
                'offset' => 10,
                'total' => 100,
                'href' => 'http://doesNotMatter.com/current',
                'next' => 'http://doesNotMatter.com/next',
                'previous' => 'http://doesNotMatter.com/previous',
            ],
        ]);

        $array = $metadata->toArray();

        $expected = [
            'limit' => 20,
            'offset' => 10,
            'total' => 100,
            'current' => 'http://doesNotMatter.com/current',
            'next' => 'http://doesNotMatter.com/next',
            'previous' => 'http://doesNotMatter.com/previous',
        ];

        $this->assertSame($expected, $array);
    }
}
