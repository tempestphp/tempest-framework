<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final readonly class UriGeneratorController
{
    #[Get('/test-with-collision/{idea}')]
    public function withCollidingNames(string $idea): Response
    {
        return new Ok();
    }
}
