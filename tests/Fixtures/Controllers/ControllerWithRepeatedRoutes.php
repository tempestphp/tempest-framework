<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Method;
use Tempest\Http\Post;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Route;

final readonly class ControllerWithRepeatedRoutes
{
    #[Get('/repeated/c')]
    #[Get('/repeated/d')]
    #[Post('/repeated/e')]
    #[Post('/repeated/f')]
    #[Route('/repeated/a', Method::GET)]
    #[Route('/repeated/b', Method::GET)]
    public function __invoke(): Response
    {
        return new Ok();
    }
}
