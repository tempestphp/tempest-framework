<?php

namespace Tests\Tempest\Route;

use Tempest\Interfaces\Request;
use Tempest\Interfaces\Router;
use Tempest\Http\Method;
use Tempest\Http\GenericRequest;
use Tempest\Http\Status;
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

        $request = $this->container->get(GenericRequest::class);

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

        $response = $router->dispatch(GenericRequest::post('/create-post', $body));

        $this->assertEquals(Status::HTTP_200, $response->status);
        $this->assertEquals('test-title test-text', $response->body);
    }
}
