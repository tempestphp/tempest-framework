<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Method;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;
use Tempest\Router\Route;

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
