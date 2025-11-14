<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Stateless;

final class StatelessController
{
    #[Stateless]
    #[Get('/stateless')]
    public function __invoke(): Ok
    {
        return new Ok();
    }
}
