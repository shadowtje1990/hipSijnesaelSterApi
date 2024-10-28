<?php

declare(strict_types=1);

namespace App\Utils;

interface BearerTokenProvider
{
    public function token(): string;
}
