<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\RateLimit\Http\Throttle;
use Tempest\Router\Get;

#[Throttle(attempts: 100, bucket: 'controller-shared')]
final readonly class SharedBucketThrottledController
{
    #[Throttle(attempts: 1, bucket: 'controller-shared')]
    #[Get('/class-throttled/shared-bucket')]
    public function index(): Response
    {
        return new Ok('allowed');
    }
}
