<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Request;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Post;

final class JsonController
{
    #[Post('/json-endpoint')]
    public function __invoke(Request $request): \Tempest\Http\Responses\Ok
    {
        return new Ok($request->get('title'));
    }
}
