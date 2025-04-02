<?php

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\Middleware;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_middleware_construct(): void
    {
        $middleware = new Middleware('a', 'b', 'c');

        $this->assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], iterator_to_array($middleware));
    }

    public function test_remove_middleware(): void
    {
        $middleware = new Middleware('a', 'b', 'c')->removeMiddleware('b');

        $this->assertSame(['a' => 'a', 'c' => 'c'], iterator_to_array($middleware));
    }
}