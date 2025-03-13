<?php

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Post;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;
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