<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Post;

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
