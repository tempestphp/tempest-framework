<?php

namespace Tests\Tempest\Route;

use App\Controllers\TestController;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericRouter;
use Tempest\Http\Status;
use Tests\Tempest\TestCase;

class RouterTest extends TestCase
{
    /** @test */
    public function test_dispatch()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(GenericRequest::get('/test'));

        $this->assertEquals(Status::HTTP_200, $response->status);
        $this->assertEquals('test', $response->body);
    }

    /** @test */
    public function test_dispatch_with_parameter()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(GenericRequest::get('/test/1/a'));

        $this->assertEquals(Status::HTTP_200, $response->status);
        $this->assertEquals('1a', $response->body);
    }

    /** @test */
    public function test_generate_uri()
    {
        $router = $this->container->get(GenericRouter::class);

        $this->assertEquals('/test/1/a', $router->toUri(TestController::class, method: 'withParams', id: 1, name: 'a'));
        $this->assertEquals('/test', $router->toUri(TestController::class));
    }

    /** @test */
    public function test_with_view()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(GenericRequest::get('/view'));

        $this->assertEquals(Status::HTTP_200, $response->status);

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>
Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $response->body);
    }
}
