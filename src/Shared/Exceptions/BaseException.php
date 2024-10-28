<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

class BaseException extends AbstractException
{
    protected int $httpStatusCode = 500;
}
