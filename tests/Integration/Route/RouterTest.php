<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use App\Controllers\TestController;
use App\Controllers\TestGlobalMiddleware;
use Tempest\Http\GenericRouter;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class RouterTest extends FrameworkIntegrationTest
{
    public function test_dispatch()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('test', $response->getBody());
    }

    public function test_dispatch_with_parameter()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('1a', $response->getBody());
    }

    public function test_generate_uri()
    {
        $router = $this->container->get(GenericRouter::class);

        $this->assertEquals('/test/1/a', $router->toUri([TestController::class, 'withParams'], id: 1, name: 'a'));
        $this->assertEquals('/test', $router->toUri(TestController::class));
    }

    public function test_with_view()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/view'));

        $this->assertInstanceOf(Ok::class, $response);
    }

    public function test_route_binding()
    {
        $this->markTestSkipped('Broken, need to debug');

        //        $this->migrate(
        //            CreateMigrationsTable::class,
        //            CreateBookTable::class,
        //            CreateAuthorTable::class,
        //        );
        //
        //        Book::create(
        //            title: 'Test',
        //            author: new Author(name: 'Brent'),
        //        );
        //
        //        $router = $this->container->get(Router::class);
        //
        //        $response = $router->dispatch($this->http->makePsrRequest('/books/1'));
        //
        //        $this->assertSame(Status::OK, $response->getStatus());
        //        $this->assertSame('Test', $response->getBody());
    }

    public function test_middleware()
    {
        $router = $this->container->get(GenericRouter::class);

        $router->addMiddleware(TestGlobalMiddleware::class);

        $response = $router->dispatch($this->http->makePsrRequest('/with-middleware'));

        $this->assertEquals(['from-dependency'], $response->getHeader('middleware')->values);
        $this->assertEquals(['yes'], $response->getHeader('global-middleware')->values);
    }
}
