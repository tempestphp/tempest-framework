<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\WithMiddleware;

#[WithMiddleware(TestMiddleware::class)]
final class WithMiddlewareController
{
    #[Get('/with-decorated-middleware')]
    public function __invoke(): Ok
    {
        return new Ok();
    }
}
