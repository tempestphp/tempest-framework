<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Http\Session\VerifyCsrfMiddleware;
use Tempest\Router\Get;
use Tempest\Router\SetCookieMiddleware;
use Tempest\Router\WithoutMiddleware;

#[WithoutMiddleware(VerifyCsrfMiddleware::class, SetCookieMiddleware::class)]
final class WithoutMiddlewareController
{
    #[Get('/without-decorated-middleware')]
    public function __invoke(): Ok
    {
        return new Ok();
    }
}
