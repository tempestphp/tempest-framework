<?php

namespace Tempest\Upgrade\Tests\Tempest28\Fixtures;

use Tempest\Http\Method;
use Tempest\Router\Route;

final readonly class CustomRoute implements Route
{
    public function __construct(
        public Method $method,
        public string $uri,
        public array $middleware = [],
        public array $without = [],
    ) {}
}