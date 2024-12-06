<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;

final readonly class ControllerWithRepeatedRoutes
{
    #[Get('/repeated/a')]
    #[Get('/repeated/b')]
    #[Get('/repeated/c')]
    #[Get('/repeated/d')]
    #[Post('/repeated/e')]
    #[Post('/repeated/f')]
    public function __invoke(): Response
    {
        return new Ok();
    }
}
