<?php

declare(strict_types=1);

namespace Test\Unit\Shared\Traits;

use PHPUnit\Framework\TestCase;
use App\Shared\Traits\ValuesFilter;

class ValuesFilterTest extends TestCase
{
    private TestableValuesFilter $valuesFilter;

    protected function setUp(): void
    {
        $this->valuesFilter = new TestableValuesFilter();
    }

    public function testFilterConvertsStringsToBooleans(): void
    {
        $input = ['true', 'false'];
        $expected = [true, false];

        $this->assertSame($expected, $this->valuesFilter->filter($input));
    }

    public function testFilterConvertsStringNullToNull(): void
    {
        $input = ['null', 'notNull'];
        $expected = [null, 'notNull'];

        $this->assertSame($expected, $this->valuesFilter->filter($input));
    }

    public function testFilterTrimsStringValues(): void
    {
        $input = ['  test  ', ' 123 '];
        $expected = ['test', 123];

        $this->assertSame($expected, $this->valuesFilter->filter($input));
    }

    public function testFilterConvertsNumericStringsToNumbers(): void
    {
        $input = ['123', '45.67'];
        $expected = [123, 45.67];

        $this->assertSame($expected, $this->valuesFilter->filter($input));
    }

    public function testFilterLeavesNonSpecialValuesUnchanged(): void
    {
        $input = ['string', 456, true, null, 78.9];
        $expected = ['string', 456, true, null, 78.9];

        $this->assertSame($expected, $this->valuesFilter->filter($input));
    }
}

/**
 * A testable class that uses the ValuesFilter trait.
 */
class TestableValuesFilter
{
    use ValuesFilter;
}
