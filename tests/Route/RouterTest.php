<?php

declare(strict_types=1);

namespace Tests\Tempest\Route;

use App\Controllers\TestController;
use Tempest\Http\GenericRouter;
use Tempest\Http\Status;
use Tests\Tempest\TestCase;

class RouterTest extends TestCase
{
    /** @test */
    public function test_dispatch()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(request('/test'));

        $this->assertEquals(Status::HTTP_200, $response->getStatus());
        $this->assertEquals('test', $response->getBody());
    }

    /** @test */
    public function test_dispatch_with_parameter()
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch(request('/test/1/a'));

        $this->assertEquals(Status::HTTP_200, $response->getStatus());
        $this->assertEquals('1a', $response->getBody());
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

        $response = $router->dispatch(request('/view'));

        $this->assertEquals(Status::HTTP_200, $response->getStatus());

        $expected = <<<HTML
<html lang="en">
<head>
    <title></title>
</head>
<body>
Hello Brent!</body>
</html>
HTML;

        $this->assertEquals($expected, $response->getBody());
    }
}
