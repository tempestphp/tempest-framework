<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class RequestLifetimeDependency
{
    public function __construct(
        public RequestLifetimeSingleton $singleton,
    ) {}
}
