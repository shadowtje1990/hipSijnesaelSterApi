<?php

namespace App\Authentication\Exceptions;

use App\Shared\Exceptions\BaseException;

class SpotifyApiException extends BaseException
{
    public const TOKEN_EXPIRED = 'The access token expired';
    public const RATE_LIMIT_STATUS = 429;

    public function hasExpiredToken(string $message): bool
    {
        return self::TOKEN_EXPIRED === $message;
    }

    public function isRateLimited(): bool
    {
        return self::RATE_LIMIT_STATUS === $this->getCode();
    }
}
