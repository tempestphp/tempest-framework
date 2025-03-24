<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Post;
use Tempest\Router\Request;
use Tempest\Router\Responses\Ok;

final class JsonController
{
    #[Post('/json-endpoint')]
    public function __invoke(Request $request): \Tempest\Router\Responses\Ok
    {
        return new Ok($request->get('title'));
    }
}
