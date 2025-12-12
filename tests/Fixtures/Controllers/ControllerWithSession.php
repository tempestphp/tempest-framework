<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Responses\Ok;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;

final class ControllerWithSession
{
    #[Get('/controller-with-session')]
    public function __invoke(Session $session): Ok
    {
        $session->flash('test', 'hi');

        return new Ok();
    }
}