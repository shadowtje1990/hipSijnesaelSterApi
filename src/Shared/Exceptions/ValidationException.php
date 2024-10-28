<?php declare(strict_types=1);

namespace App\Shared\Exceptions;

class ValidationException extends AbstractException
{
    protected int $httpStatusCode = 400;
}