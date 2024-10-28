<?php declare(strict_types=1);

namespace App\Shared\Exceptions;

class NotFoundException extends AbstractException
{
    protected int $httpStatusCode = 404;
}
