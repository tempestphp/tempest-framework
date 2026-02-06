<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Request;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final class HeaderWithUnderscoresController
{
    #[Get('/header-with-underscores')]
    public function __invoke(Request $request): Ok
    {
        return new Ok()->addHeader('tempest_session_id', $request->headers->get('tempest_session_id'));
    }
}
