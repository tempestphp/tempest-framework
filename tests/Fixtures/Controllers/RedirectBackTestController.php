<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Back;
use Tempest\Router\Get;

final class RedirectBackTestController
{
    #[Get('/test-redirect-back-url')]
    public function formAction(Request $request): Response
    {
        return new Back();
    }
}
