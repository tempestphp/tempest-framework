<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\RoutingTree;
use Tempest\Router\Routing\Matching\GenericRouteMatcher;

/**
 * @internal
 */
final class OptionalParametersTest extends TestCase
{
    public function test_route_with_optional_parameter_matches_both_paths(): void
    {
        $routeBuilder = new FakeRouteBuilderWithOptionalParams();
        $tree = new RoutingTree();

        $markedRoute = $routeBuilder
            ->withUri('/users/{id?}')
            ->withHandler('handlerWithOptionalId')
            ->asMarkedRoute('a');

        $tree->add($markedRoute);
        $regexes = $tree->toMatchingRegexes();

        $routeConfig = new RouteConfig(
            staticRoutes: [],
            dynamicRoutes: [
                'GET' => [
                    'a' => $markedRoute->route,
                ],
            ],
            matchingRegexes: $regexes,
        );

        $matcher = new GenericRouteMatcher($routeConfig);

        $matchedWithoutParam = $matcher->match(new GenericRequest(Method::GET, '/users'));
        $this->assertNotNull($matchedWithoutParam);
        $this->assertEquals('/users/{id?}', $matchedWithoutParam->route->uri);
        $this->assertEquals(['id' => 'default-id'], $matchedWithoutParam->params);

        $matchedWithParam = $matcher->match(new GenericRequest(Method::GET, '/users/123'));
        $this->assertNotNull($matchedWithParam);
        $this->assertEquals('/users/{id?}', $matchedWithParam->route->uri);
        $this->assertEquals(['id' => '123'], $matchedWithParam->params);
    }

    public function test_route_with_multiple_optional_parameters(): void
    {
        $routeBuilder = new FakeRouteBuilderWithOptionalParams();
        $tree = new RoutingTree();

        $markedRoute = $routeBuilder
            ->withUri('/posts/{id?}/{slug?}')
            ->withHandler('handlerWithTwoOptionalParams')
            ->asMarkedRoute('a');

        $tree->add($markedRoute);
        $regexes = $tree->toMatchingRegexes();

        $routeConfig = new RouteConfig(
            staticRoutes: [],
            dynamicRoutes: [
                'GET' => [
                    'a' => $markedRoute->route,
                ],
            ],
            matchingRegexes: $regexes,
        );

        $matcher = new GenericRouteMatcher($routeConfig);

        $matchedNoParams = $matcher->match(new GenericRequest(Method::GET, '/posts'));
        $this->assertNotNull($matchedNoParams);
        $this->assertEquals(['id' => 'default-id', 'slug' => 'default-slug'], $matchedNoParams->params);

        $matchedOneParam = $matcher->match(new GenericRequest(Method::GET, '/posts/123'));
        $this->assertNotNull($matchedOneParam);
        $this->assertEquals(['id' => '123', 'slug' => 'default-slug'], $matchedOneParam->params);

        $matchedTwoParams = $matcher->match(new GenericRequest(Method::GET, '/posts/123/my-post'));
        $this->assertNotNull($matchedTwoParams);
        $this->assertEquals(['id' => '123', 'slug' => 'my-post'], $matchedTwoParams->params);
    }

    public function test_route_with_required_and_optional_parameters(): void
    {
        $routeBuilder = new FakeRouteBuilderWithOptionalParams();
        $tree = new RoutingTree();

        $markedRoute = $routeBuilder
            ->withUri('/posts/{id}/{slug?}')
            ->withHandler('handlerWithRequiredAndOptional')
            ->asMarkedRoute('a');

        $tree->add($markedRoute);
        $regexes = $tree->toMatchingRegexes();

        $routeConfig = new RouteConfig(
            staticRoutes: [],
            dynamicRoutes: [
                'GET' => [
                    'a' => $markedRoute->route,
                ],
            ],
            matchingRegexes: $regexes,
        );

        $matcher = new GenericRouteMatcher($routeConfig);

        $matchedRequired = $matcher->match(new GenericRequest(Method::GET, '/posts/123'));
        $this->assertNotNull($matchedRequired);
        $this->assertEquals(['id' => '123', 'slug' => 'default-slug'], $matchedRequired->params);

        $matchedBoth = $matcher->match(new GenericRequest(Method::GET, '/posts/123/my-post'));
        $this->assertNotNull($matchedBoth);
        $this->assertEquals(['id' => '123', 'slug' => 'my-post'], $matchedBoth->params);
    }

    public function test_route_with_optional_parameter_and_custom_regex(): void
    {
        $routeBuilder = new FakeRouteBuilderWithOptionalParams();
        $tree = new RoutingTree();

        $markedRoute = $routeBuilder
            ->withUri('/users/{id?:\d+}')
            ->withHandler('handlerWithOptionalId')
            ->asMarkedRoute('a');

        $tree->add($markedRoute);
        $regexes = $tree->toMatchingRegexes();

        $routeConfig = new RouteConfig(
            staticRoutes: [],
            dynamicRoutes: [
                'GET' => [
                    'a' => $markedRoute->route,
                ],
            ],
            matchingRegexes: $regexes,
        );

        $matcher = new GenericRouteMatcher($routeConfig);

        $matchedWithoutParam = $matcher->match(new GenericRequest(Method::GET, '/users'));
        $this->assertNotNull($matchedWithoutParam);
        $this->assertEquals(['id' => 'default-id'], $matchedWithoutParam->params);

        $matchedWithNumeric = $matcher->match(new GenericRequest(Method::GET, '/users/123'));
        $this->assertNotNull($matchedWithNumeric);
        $this->assertEquals(['id' => '123'], $matchedWithNumeric->params);

        $matchedWithNonNumeric = $matcher->match(new GenericRequest(Method::GET, '/users/abc'));
        $this->assertNull($matchedWithNonNumeric);
    }

    public function test_optional_parameters_are_parsed_correctly(): void
    {
        $routeBuilder = new FakeRouteBuilderWithOptionalParams();

        $route = $routeBuilder
            ->withUri('/users/{id?}')
            ->withHandler('handlerWithOptionalId')
            ->asDiscoveredRoute();

        $this->assertEquals(['id'], $route->parameters);
        $this->assertEquals(['id' => true], $route->optionalParameters);
    }

    public function test_mixed_optional_and_required_parameters_are_parsed_correctly(): void
    {
        $routeBuilder = new FakeRouteBuilderWithOptionalParams();

        $route = $routeBuilder
            ->withUri('/posts/{id}/{slug?}')
            ->withHandler('handlerWithRequiredAndOptional')
            ->asDiscoveredRoute();

        $this->assertEquals(['id', 'slug'], $route->parameters);
        $this->assertEquals(['id' => false, 'slug' => true], $route->optionalParameters);
    }
}
