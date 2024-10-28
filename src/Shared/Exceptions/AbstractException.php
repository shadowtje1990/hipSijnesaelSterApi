<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class AbstractException extends \Exception implements HttpExceptionInterface
{
    protected int $httpStatusCode = 500;

    public function getStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
