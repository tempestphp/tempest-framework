<?php

declare(strict_types=1);

namespace Tests\Tempest\Route;

use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Router;
use Tests\Tempest\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function from_container()
    {
        $this->server(
            method: Method::POST,
            uri: '/test',
            body: ['test'],
        );

        $request = $this->container->get(Request::class);

        $this->assertEquals(Method::POST, $request->method);
        $this->assertEquals('/test', $request->uri);
        $this->assertEquals(['test'], $request->body);
    }

    /** @test */
    public function custom_request_test()
    {
        $router = $this->container->get(Router::class);

        $body = [
            'title' => 'test-title',
            'text' => 'test-text',
        ];

        $this->server(
            method: Method::POST,
            uri: '/test',
            body: $body,
        );

        $response = $router->dispatch(request('/create-post')->post($body));

        $this->assertEquals(Status::HTTP_200, $response->getStatus());
        $this->assertEquals('test-title test-text', $response->getBody());
    }
}
