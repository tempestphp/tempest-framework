<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final readonly class IpController
{
    #[Get('/ip')]
    public function __invoke(Request $request): Response
    {
        return new Ok($request->ip?->toString() ?? 'unknown');
    }
}
