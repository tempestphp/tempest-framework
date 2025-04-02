<?php

namespace Tests\Tempest\Integration\Core;

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
    public function test_middleware_construct(): void
    {
        $middleware = new Middleware(
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareC::class,
        );

        $middlewareAsArray = iterator_to_array($middleware);

        $this->assertSame([
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareC::class,
        ], array_keys($middlewareAsArray));

        $this->assertInstanceOf(ClassReflector::class, $middlewareAsArray[MiddlewareA::class]);
        $this->assertSame(MiddlewareA::class, $middlewareAsArray[MiddlewareA::class]->getName());

        $this->assertInstanceOf(ClassReflector::class, $middlewareAsArray[MiddlewareB::class]);
        $this->assertSame(MiddlewareB::class, $middlewareAsArray[MiddlewareB::class]->getName());

        $this->assertInstanceOf(ClassReflector::class, $middlewareAsArray[MiddlewareC::class]);
        $this->assertSame(MiddlewareC::class, $middlewareAsArray[MiddlewareC::class]->getName());
    }

    public function test_add_middleware(): void
    {
        $middleware = new Middleware(MiddlewareA::class, MiddlewareC::class)->add(MiddlewareB::class);

        $this->assertSame([
            MiddlewareA::class,
            MiddlewareC::class,
            MiddlewareB::class,
        ], array_keys(iterator_to_array($middleware)));
    }

    public function test_remove_middleware(): void
    {
        $middleware = new Middleware(
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareC::class,
        )->remove(MiddlewareB::class);

        $this->assertSame([
            MiddlewareA::class,
            MiddlewareC::class,
        ], array_keys(iterator_to_array($middleware)));
    }

    public function test_sort_with_additions(): void
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

        $this->assertSame([
            MiddlewareFramework::class,
            MiddlewareHighest::class,
            MiddlewareHigh::class,
            MiddlewareC::class,
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareNormal::class,
            MiddlewareLow::class,
            MiddlewareLowest::class,
        ], array_keys(iterator_to_array($middleware)));
    }

    public function test_sort_with_removals_(): void
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

        $this->assertSame([
            MiddlewareHighest::class,
            MiddlewareHigh::class,
            MiddlewareC::class,
            MiddlewareB::class,
            MiddlewareNormal::class,
            MiddlewareLow::class,
        ], array_keys(iterator_to_array($middleware)));
    }

    public function test_unwrap(): void
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

        $this->assertSame(array_reverse([
            MiddlewareFramework::class,
            MiddlewareHighest::class,
            MiddlewareHigh::class,
            MiddlewareC::class,
            MiddlewareA::class,
            MiddlewareB::class,
            MiddlewareNormal::class,
            MiddlewareLow::class,
            MiddlewareLowest::class,
        ]), array_keys(iterator_to_array($middleware->unwrap())));
    }

    public function test_serialize(): void
    {
        $middleware = new Middleware(
            MiddlewareB::class,
            MiddlewareA::class,
            MiddlewareC::class,
        );

        $middleware = unserialize(serialize($middleware));

        $middlewareAsArray = iterator_to_array($middleware);

        $this->assertSame([
            MiddlewareB::class,
            MiddlewareA::class,
            MiddlewareC::class,
        ], array_keys($middlewareAsArray));
    }
}
