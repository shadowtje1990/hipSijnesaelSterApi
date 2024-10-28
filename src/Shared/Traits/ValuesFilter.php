<?php declare(strict_types=1);

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
                if ($value === 'true') {
                    $value = true;
                }
                if ($value === 'false') {
                    $value = false;
                }
                if ($value === 'null') {
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