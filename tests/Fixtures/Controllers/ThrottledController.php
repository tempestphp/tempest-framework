<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\RateLimit\Http\Throttle;
use Tempest\RateLimit\Http\ThrottleWith;
use Tempest\RateLimit\Per;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tests\Tempest\Fixtures\RateLimit\PremiumRateLimitProfile;
use Tests\Tempest\Fixtures\RateLimit\TieredRateLimitProfile;

final readonly class ThrottledController
{
    #[Throttle(attempts: 1)]
    #[Get('/throttled-by-http-method')]
    #[Post('/throttled-by-http-method')]
    public function multipleHttpMethods(): Response
    {
        return new Ok('allowed');
    }

    #[Throttle(attempts: 2)]
    #[Get('/throttled')]
    public function index(): Response
    {
        return new Ok('allowed');
    }

    #[Throttle(attempts: 3)]
    #[Throttle(attempts: 1, per: Per::DAY)]
    #[Get('/throttled-twice')]
    public function twice(): Response
    {
        return new Ok('allowed');
    }

    #[ThrottleWith(PremiumRateLimitProfile::class)]
    #[Get('/throttled-by-profile')]
    public function profile(): Response
    {
        return new Ok('allowed');
    }

    #[ThrottleWith(TieredRateLimitProfile::class)]
    #[Get('/throttled-by-tiers')]
    public function tiers(): Response
    {
        return new Ok('allowed');
    }

    #[Throttle(attempts: 2)]
    #[Throttle(attempts: 5)]
    #[Get('/throttled-by-two-identical-windows')]
    public function identicalWindows(): Response
    {
        return new Ok('allowed');
    }

    /**
     * Declares the same allowance twice. Both land in the same counter, so the route is throttled
     * as though one had been declared.
     */
    #[Throttle(attempts: 2)]
    #[Throttle(attempts: 2)]
    #[Get('/throttled-by-two-identical-limits')]
    public function identicalLimits(): Response
    {
        return new Ok('allowed');
    }

    #[Throttle(attempts: 2, bucket: 'shared')]
    #[Get('/throttled-by-shared-bucket/first')]
    public function sharedBucketFirst(): Response
    {
        return new Ok('allowed');
    }

    /**
     * Names the same bucket as `sharedBucketFirst`, so both routes spend from a single allowance.
     */
    #[Throttle(attempts: 2, bucket: 'shared')]
    #[Get('/throttled-by-shared-bucket/second')]
    public function sharedBucketSecond(): Response
    {
        return new Ok('allowed');
    }

    /**
     * Declares the wider window first. Consuming limits in declaration order would burn the daily
     * allowance on requests the per-minute limit already rejected.
     */
    #[Throttle(attempts: 3, per: Per::DAY)]
    #[Throttle(attempts: 2)]
    #[Get('/throttled-widest-first')]
    public function widestFirst(): Response
    {
        return new Ok('allowed');
    }

    #[Get('/not-throttled')]
    public function unlimited(): Response
    {
        return new Ok('allowed');
    }
}
