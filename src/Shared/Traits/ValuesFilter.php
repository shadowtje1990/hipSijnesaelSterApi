<?php

declare(strict_types=1);

namespace App\Shared\Traits;

trait ValuesFilter
{
    public function filter(array $values): array
    {
        return array_map(
            function ($value) {
                if (is_string($value)) {
                    $value = trim($value);
                }
                if ('true' === $value) {
                    $value = true;
                }
                if ('false' === $value) {
                    $value = false;
                }
                if ('null' === $value) {
                    $value = null;
                }
                if (is_string($value) && is_numeric($value)) {
                    $value = $this->makeCorrectNumericFromString($value);
                }

                return $value;
            },
            $values
        );
    }

    /**
     * @return int|float
     */
    private function makeCorrectNumericFromString($value)
    {
        return $value + 0;
    }
}
