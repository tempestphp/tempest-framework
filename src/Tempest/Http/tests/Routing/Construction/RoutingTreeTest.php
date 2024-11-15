<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Routing\Construction;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Http\Route;
use Tempest\Http\Routing\Construction\DuplicateRouteException;
use Tempest\Http\Routing\Construction\MarkedRoute;
use Tempest\Http\Routing\Construction\RoutingTree;
use Tempest\Http\Routing\Matching\MatchingRegex;

/**
 * @internal
 */
final class RoutingTreeTest extends TestCase
{
    public function test_empty_tree(): void
    {
        $subject = new RoutingTree();
        $this->assertEquals([], $subject->toMatchingRegexes());
    }

    public function test_add_throws_on_duplicated_routes(): void
    {
        $subject = new RoutingTree();
        $this->expectException(DuplicateRouteException::class);

        $subject->add(new MarkedRoute('a', new Route('/', Method::GET)));
        $subject->add(new MarkedRoute('b', new Route('/', Method::GET)));
    }

    public function test_multiple_routes(): void
    {
        $subject = new RoutingTree();
        $subject->add(new MarkedRoute('a', new Route('/', Method::GET)));
        $subject->add(new MarkedRoute('b', new Route('/{id}/hello/{name}', Method::GET)));
        $subject->add(new MarkedRoute('c', new Route('/{id}/hello/brent', Method::GET)));
        $subject->add(new MarkedRoute('d', new Route('/{greeting}/{name}', Method::GET)));
        $subject->add(new MarkedRoute('e', new Route('/{greeting}/brent', Method::GET)));

        $this->assertEquals([
            'GET' => new MatchingRegex([
                '#^(?|\/?$(*MARK:a)|/([^/]++)(?|/brent\/?$(*MARK:e)|/hello(?|/brent\/?$(*MARK:c)|/([^/]++)\/?$(*MARK:b))|/([^/]++)\/?$(*MARK:d)))#',
            ]),
        ], $subject->toMatchingRegexes());
    }

    public function test_chunked_routes(): void
    {
        $subject = new RoutingTree();
        $mark = 'a';

        for ($i = 0; $i <= 1000; $i++) {
            $mark = str_increment($mark);
            $subject->add(new MarkedRoute($mark, new Route("/test/{$i}/route_{$i}", Method::GET)));
        }

        $matchingRegexes = $subject->toMatchingRegexes()['GET'];
        $this->assertGreaterThan(1, count($matchingRegexes->patterns));

        $this->assertNotNull($matchingRegexes->match('/test/0/route_0'));
        $this->assertNotNull($matchingRegexes->match('/test/1000/route_1000'));
    }

    public function test_multiple_http_methods(): void
    {
        $subject = new RoutingTree();
        $subject->add(new MarkedRoute('a', new Route('/', Method::GET)));
        $subject->add(new MarkedRoute('b', new Route('/', Method::POST)));

        $this->assertEquals([
            'GET' => new MatchingRegex(['#^\/?$(*MARK:a)#']),
            'POST' => new MatchingRegex(['#^\/?$(*MARK:b)#']),
        ], $subject->toMatchingRegexes());
    }
}
