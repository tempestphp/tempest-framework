<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;

final readonly class UriGeneratorController
{
    #[Get('/test-with-collision/{idea}')]
    public function withCollidingNames(string $idea): Response
    {
        return new Ok();
    }
}
