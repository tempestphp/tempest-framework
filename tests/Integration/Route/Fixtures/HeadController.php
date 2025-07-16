<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Head;

final class HeadController
{
    #[Get('/implicit-head')]
    public function implicitHead(): Response
    {
        return new Ok('body')->addHeader('x-custom', 'true');
    }

    #[Head('/explicit-head')]
    public function explicitHead(): Response
    {
        return new Ok()->addHeader('x-custom', 'true');
    }
}
