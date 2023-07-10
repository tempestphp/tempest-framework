<?php

namespace Tests\Tempest\Route;

use Tempest\Route\Method;
use Tempest\Route\Request;
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

        /** @var Request $request */
        $request = $this->container->get(Request::class);

        $this->assertEquals(Method::POST, $request->method);
        $this->assertEquals('/test', $request->uri);
        $this->assertEquals(['test'], $request->body);
    }
}
