<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;

final readonly class UriGeneratorController
{
    #[Get('/test-with-collision/{idea}')]
    public function withCollidingNames(string $idea): Response
    {
        return new Ok();
    }
}
