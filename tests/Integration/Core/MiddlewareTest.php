<?php

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Core\Middleware;
use Tempest\Reflection\ClassReflector;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareA;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareB;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareC;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareFramework;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareHigh;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareHighest;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareLow;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareLowest;
use Tests\Tempest\Integration\Core\Fixtures\MiddlewareNormal;

final class MiddlewareTest extends TestCase
{
    #[Test]
    public function middleware_construct(): void
    {
        $middleware = new Middleware(
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareC::class,
        );

        $middlewareAsArray = iterator_to_array($middleware);

        $this->assertSame(
            expected: [
                MiddlewareA::class,
                MiddlewareB::class,
                MiddlewareC::class,
            ],
            actual: array_keys($middlewareAsArray),
        );

        $this->assertInstanceOf(ClassReflector::class, $middlewareAsArray[MiddlewareA::class]);
        $this->assertSame(MiddlewareA::class, $middlewareAsArray[MiddlewareA::class]->getName());

        $this->assertInstanceOf(ClassReflector::class, $middlewareAsArray[MiddlewareB::class]);
        $this->assertSame(MiddlewareB::class, $middlewareAsArray[MiddlewareB::class]->getName());

        $this->assertInstanceOf(ClassReflector::class, $middlewareAsArray[MiddlewareC::class]);
        $this->assertSame(MiddlewareC::class, $middlewareAsArray[MiddlewareC::class]->getName());
    }

    #[Test]
    public function add_middleware(): void
    {
        $middleware = new Middleware(MiddlewareA::class, MiddlewareC::class)->add(MiddlewareB::class);

        $this->assertSame(
            expected: [
                MiddlewareA::class,
                MiddlewareC::class,
                MiddlewareB::class,
            ],
            actual: array_keys(iterator_to_array($middleware)),
        );
    }

    #[Test]
    public function remove_middleware(): void
    {
        $middleware = new Middleware(
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareC::class,
        )->remove(MiddlewareB::class);

        $this->assertSame(
            expected: [
                MiddlewareA::class,
                MiddlewareC::class,
            ],
            actual: array_keys(iterator_to_array($middleware)),
        );
    }

    #[Test]
    public function sort_with_additions(): void
    {
        $middleware = new Middleware(
            MiddlewareC::class,
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareHigh::class,
            MiddlewareHighest::class,
            MiddlewareNormal::class,
            MiddlewareLowest::class,
            MiddlewareLow::class,
            MiddlewareFramework::class,
        );

        $this->assertSame(
            expected: [
                MiddlewareFramework::class,
                MiddlewareHighest::class,
                MiddlewareHigh::class,
                MiddlewareC::class,
                MiddlewareA::class,
                MiddlewareB::class,
                MiddlewareNormal::class,
                MiddlewareLow::class,
                MiddlewareLowest::class,
            ],
            actual: array_keys(iterator_to_array($middleware)),
        );
    }

    #[Test]
    public function sort_with_removals_(): void
    {
        $middleware = new Middleware(
            MiddlewareC::class,
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareHigh::class,
            MiddlewareHighest::class,
            MiddlewareNormal::class,
            MiddlewareLowest::class,
            MiddlewareLow::class,
            MiddlewareFramework::class,
        )->remove(MiddlewareLowest::class, MiddlewareFramework::class, MiddlewareA::class);

        $this->assertSame(
            expected: [
                MiddlewareHighest::class,
                MiddlewareHigh::class,
                MiddlewareC::class,
                MiddlewareB::class,
                MiddlewareNormal::class,
                MiddlewareLow::class,
            ],
            actual: array_keys(iterator_to_array($middleware)),
        );
    }

    #[Test]
    public function unwrap(): void
    {
        $middleware = new Middleware(
            MiddlewareC::class,
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareHigh::class,
            MiddlewareHighest::class,
            MiddlewareNormal::class,
            MiddlewareLowest::class,
            MiddlewareLow::class,
            MiddlewareFramework::class,
        );

        $this->assertSame(
            array_reverse([
                MiddlewareFramework::class,
                MiddlewareHighest::class,
                MiddlewareHigh::class,
                MiddlewareC::class,
                MiddlewareA::class,
                MiddlewareB::class,
                MiddlewareNormal::class,
                MiddlewareLow::class,
                MiddlewareLowest::class,
            ]),
            actual: array_keys(iterator_to_array($middleware->unwrap())),
        );
    }

    #[Test]
    public function serialize(): void
    {
        $middleware = new Middleware(
            MiddlewareB::class,
            MiddlewareA::class,
            MiddlewareC::class,
        );

        $middleware = unserialize(serialize($middleware));

        $middlewareAsArray = iterator_to_array($middleware);

        $this->assertSame(
            expected: [
                MiddlewareB::class,
                MiddlewareA::class,
                MiddlewareC::class,
            ],
            actual: array_keys($middlewareAsArray),
        );
    }
}
