<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Routing\Construction;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Http\Route;
use Tempest\Http\Routing\Construction\DuplicateRouteException;
use Tempest\Http\Routing\Construction\MarkedRoute;
use Tempest\Http\Routing\Construction\RoutingTree;

/**
 * @internal
 */
final class RoutingTreeTest extends TestCase
{
    private RoutingTree $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RoutingTree();
    }

    public function test_empty_tree(): void
    {
        $this->assertEquals([], $this->subject->toMatchingRegexes());
    }

    public function test_add_throws_on_duplicated_routes(): void
    {
        $this->expectException(DuplicateRouteException::class);

        $this->subject->add(new MarkedRoute('a', new Route('/', Method::GET)));
        $this->subject->add(new MarkedRoute('b', new Route('/', Method::GET)));
    }

    public function test_multiple_routes(): void
    {
        $this->subject->add(new MarkedRoute('a', new Route('/', Method::GET)));
        $this->subject->add(new MarkedRoute('b', new Route('/{id}/hello/{name}', Method::GET)));
        $this->subject->add(new MarkedRoute('c', new Route('/{id}/hello/brent', Method::GET)));
        $this->subject->add(new MarkedRoute('d', new Route('/{greeting}/{name}', Method::GET)));
        $this->subject->add(new MarkedRoute('e', new Route('/{greeting}/brent', Method::GET)));

        $this->assertEquals([
            'GET' => '#^(?|/([^/]++)(?|/hello(?|/brent\/?$(*MARK:c)|/([^/]++)\/?$(*MARK:b))|/brent\/?$(*MARK:e)|/([^/]++)\/?$(*MARK:d))|\/?$(*MARK:a))#',
        ], $this->subject->toMatchingRegexes());
    }

    public function test_multiple_http_methods(): void
    {
        $this->subject->add(new MarkedRoute('a', new Route('/', Method::GET)));
        $this->subject->add(new MarkedRoute('b', new Route('/', Method::POST)));

        $this->assertEquals([
            'GET' => '#^\/?$(*MARK:a)#',
            'POST' => '#^\/?$(*MARK:b)#',
        ], $this->subject->toMatchingRegexes());
    }
}
