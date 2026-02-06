<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\RateLimit;
use Tempest\Router\RateLimitBy;

final class RateLimitedController
{
    #[Get('/rate-limited')]
    #[RateLimit(maxAttempts: 3, decaySeconds: 60)]
    public function limited(): Response
    {
        return new Ok('success');
    }

    #[Get('/rate-limited-by-user')]
    #[RateLimit(maxAttempts: 5, decaySeconds: 60, by: RateLimitBy::USER)]
    public function limitedByUser(): Response
    {
        return new Ok('success');
    }

    #[Get('/rate-limited-custom-key')]
    #[RateLimit(maxAttempts: 2, decaySeconds: 30, key: 'custom-api')]
    public function limitedCustomKey(): Response
    {
        return new Ok('success');
    }

    #[Get('/no-rate-limit')]
    public function noLimit(): Response
    {
        return new Ok('success');
    }
}
