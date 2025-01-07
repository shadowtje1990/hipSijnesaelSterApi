<?php

namespace Tests\Unit\Playlist\Domain;

use App\Playlist\Domain\NameIdentifier;
use PHPUnit\Framework\TestCase;

class NameIdentifierTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $name = 'My Playlist';
        $nameIdentifier = NameIdentifier::fromString($name);

        $this->assertInstanceOf(NameIdentifier::class, $nameIdentifier);
        $this->assertEquals('playlist-My_Playlist', $nameIdentifier->value);
    }

    public function testCreateFromStringWithSpecialCharacters(): void
    {
        $name = 'Playlist!@#';
        $nameIdentifier = NameIdentifier::fromString($name);

        $this->assertEquals('playlist-Playlist!@#', $nameIdentifier->value);
    }

    public function testEmptyNameIdentifier(): void
    {
        $nameIdentifier = NameIdentifier::empty();

        $this->assertInstanceOf(NameIdentifier::class, $nameIdentifier);
        $this->assertTrue($nameIdentifier->isEmpty());
        $this->assertEquals('', $nameIdentifier->value);
    }

    public function testIsEmptyReturnsFalseForNonEmptyValue(): void
    {
        $nameIdentifier = NameIdentifier::fromString('Valid Name');

        $this->assertFalse($nameIdentifier->isEmpty());
    }

    public function testToStringMethod(): void
    {
        $nameIdentifier = NameIdentifier::fromString('Another Playlist');

        $this->assertEquals('playlist-Another_Playlist', (string) $nameIdentifier);
    }

    public function testToStringMethodForEmptyIdentifier(): void
    {
        $nameIdentifier = NameIdentifier::empty();

        $this->assertEquals('', (string) $nameIdentifier);
    }
}
