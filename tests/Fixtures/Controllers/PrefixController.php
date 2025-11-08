<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Prefix;

#[Prefix('/prefix')]
final class PrefixController
{
    #[Get('/endpoint')]
    public function __invoke(): Ok
    {
        return new Ok();
    }
}
