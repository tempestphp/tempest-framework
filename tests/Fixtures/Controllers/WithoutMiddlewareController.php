<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\SetCookieHeadersMiddleware;
use Tempest\Router\WithoutMiddleware;

#[WithoutMiddleware(SetCookieHeadersMiddleware::class)]
final class WithoutMiddlewareController
{
    #[Get('/without-decorated-middleware')]
    public function __invoke(): Ok
    {
        return new Ok();
    }
}
