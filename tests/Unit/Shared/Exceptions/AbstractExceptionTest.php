<?php

declare(strict_types=1);

namespace Test\Unit\Shared\Exceptions;

use PHPUnit\Framework\TestCase;
use App\Shared\Exceptions\AbstractException;

class AbstractExceptionTest extends TestCase
{
    private ConcreteException $exception;

    protected function setUp(): void
    {
        $this->exception = new ConcreteException('Test Exception', 0, null);
    }

    public function testGetStatusCodeReturnsDefaultHttpStatusCode(): void
    {
        $this->assertSame(500, $this->exception->getStatusCode());
    }

    public function testGetHeadersReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->exception->getHeaders());
    }
}

/**
 * A concrete implementation of AbstractException for testing purposes.
 */
class ConcreteException extends AbstractException
{
    // This mock class uses default behavior from AbstractException
}
