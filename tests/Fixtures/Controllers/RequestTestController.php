<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Post;
use Tests\Tempest\Fixtures\Requests\FormRequestA;

final class RequestTestController
{
    #[Post('/request-test/form')]
    public function formAction(FormRequestA $request): Response
    {
        return new Ok()
            ->addHeader('name', $request->name)
            ->addHeader('b.name', $request->b->name);
    }
}
