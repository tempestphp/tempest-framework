<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class RouteConfigTest extends TestCase
{
    private RouteConfig $subject;

    private MethodReflector $dummyMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RouteConfig();
        $this->dummyMethod = new MethodReflector(new ReflectionMethod($this, 'dummyMethod'));
    }

    public function test_empty(): void
    {
        $this->assertEquals([], $this->subject->dynamicRoutes);
        $this->assertEquals([], $this->subject->matchingRegexes);
        $this->assertEquals([], $this->subject->staticRoutes);
    }

    public function test_matching_regexes_is_updated_using_prepare_method(): void
    {
        $this->subject->addRoute($this->dummyMethod, new Route('/{id}', Method::GET));

        $this->assertEquals([], $this->subject->matchingRegexes);
        $this->subject->prepareMatchingRegexes();
        $this->assertNotEquals([], $this->subject->matchingRegexes);
    }

    public function test_adding_static_routes(): void
    {
        $routes = [
            new Route('/1', Method::GET),
            new Route('/2', Method::POST),
            new Route('/3', Method::GET),
        ];

        $this->subject->addRoute($this->dummyMethod, $routes[0]);
        $this->subject->addRoute($this->dummyMethod, $routes[1]);
        $this->subject->addRoute($this->dummyMethod, $routes[2]);

        $this->assertEquals([
            'GET' => [
                '/1' => $routes[0],
                '/1/' => $routes[0],
                '/3' => $routes[2],
                '/3/' => $routes[2],
            ],
            'POST' => [
                '/2' => $routes[1],
                '/2/' => $routes[1],
            ],
        ], $this->subject->staticRoutes);
    }

    public function test_adding_dynamic_routes(): void
    {
        $routes = [
            new Route('/{id}/1', Method::GET),
            new Route('/{id}/2', Method::POST),
            new Route('/{id}/3', Method::GET),
        ];

        $this->subject->addRoute($this->dummyMethod, $routes[0]);
        $this->subject->addRoute($this->dummyMethod, $routes[1]);
        $this->subject->addRoute($this->dummyMethod, $routes[2]);

        $this->subject->prepareMatchingRegexes();

        $this->assertEquals([
            'GET' => [
                'b' => $routes[0],
                'd' => $routes[2],
            ],
            'POST' => [
                'c' => $routes[1],
            ],
        ], $this->subject->dynamicRoutes);

        $this->assertEquals([
            'GET' => '#^(?|/([^/]++)(?|/1\/?$(*MARK:b)|/3\/?$(*MARK:d)))#',
            'POST' => '#^(?|/([^/]++)(?|/2\/?$(*MARK:c)))#',
        ], $this->subject->matchingRegexes);
    }

    public function test_serialization(): void
    {
        $routes = [
            new Route('/{id}/1', Method::GET),
            new Route('/{id}/2', Method::POST),
            new Route('/3', Method::GET),
        ];

        $this->subject->addRoute($this->dummyMethod, $routes[0]);
        $this->subject->addRoute($this->dummyMethod, $routes[1]);
        $this->subject->addRoute($this->dummyMethod, $routes[2]);

        $serialized = serialize($this->subject);
        /** @var RouteConfig $deserialized */
        $deserialized = unserialize($serialized);

        $this->assertEquals($this->subject->matchingRegexes, $deserialized->matchingRegexes);
        $this->assertEquals($this->subject->dynamicRoutes, $deserialized->dynamicRoutes);
        $this->assertEquals($this->subject->staticRoutes, $deserialized->staticRoutes);
    }

    public function dummyMethod(): Response
    {
        return new Ok();
    }
}
