<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Responses\Back;

final class RedirectBackTestController
{
    #[Get('/test-redirect-back-url')]
    public function formAction(Request $request): Response
    {
        return new Back();
    }
}
