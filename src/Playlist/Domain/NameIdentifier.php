<?php

namespace App\Playlist\Domain;

class NameIdentifier
{
    private function __construct(public readonly string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $nameIdentifier = sprintf('playlist-%s', str_replace(' ', '_', $value));

        return new self($nameIdentifier);
    }

    public static function empty(): self
    {
        return new self('');
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
