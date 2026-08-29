<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\RateLimit\Http\Throttle;
use Tempest\RateLimit\Per;
use Tempest\Router\Get;

/**
 * The controller-wide limit covers both routes, and the narrower limit on `first` applies on top of
 * it rather than replacing it.
 */
#[Throttle(attempts: 3, per: Per::HOUR)]
final readonly class ClassThrottledController
{
    #[Throttle(attempts: 1)]
    #[Get('/class-throttled/first')]
    public function first(): Response
    {
        return new Ok('allowed');
    }

    #[Get('/class-throttled/second')]
    public function second(): Response
    {
        return new Ok('allowed');
    }
}
