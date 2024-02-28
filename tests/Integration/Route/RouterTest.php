<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use App\Controllers\TestController;
use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\GenericRouter;
use Tempest\Http\Router;
use Tempest\Http\Status;
use function Tempest\request;
use Tests\Tempest\Integration\TestCase;

class RouterTest extends TestCase
{
    /** @test */
    public function test_dispatch()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(request('/test'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('test', $response->getBody());
    }

    /** @test */
    public function test_dispatch_with_parameter()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(request('/test/1/a'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('1a', $response->getBody());
    }

    /** @test */
    public function test_generate_uri()
    {
        $router = $this->container->get(GenericRouter::class);

        $this->assertEquals('/test/1/a', $router->toUri([TestController::class, 'withParams'], id: 1, name: 'a'));
        $this->assertEquals('/test', $router->toUri(TestController::class));
    }

    /** @test */
    public function test_with_view()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(request('/view'));

        $this->assertEquals(Status::OK, $response->getStatus());

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $response->getBody());
    }

    /** @test */
    public function test_route_binding()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
            CreateAuthorTable::class,
        );

        Book::create(
            title: 'Test',
            author: new Author(name: 'Brent'),
        );

        $router = $this->container->get(Router::class);

        $response = $router->dispatch(request('/books/1'));

        $this->assertSame(Status::OK, $response->getStatus());
        $this->assertSame('Test', $response->getBody());
    }

    /** @test */
    public function test_middleware()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(request('/with-middleware'));

        $this->assertEquals(['middleware' => 'from-dependency'], $response->getHeaders());
    }
}
