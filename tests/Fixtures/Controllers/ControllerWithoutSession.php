<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Stateless;

final class ControllerWithoutSession
{
    #[Stateless, Get('/controller-without-session')]
    public function __invoke(): Ok
    {
        return new Ok();
    }
}