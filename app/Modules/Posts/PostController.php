<?php

namespace App\Modules\Posts;

use Tempest\Route\Post;
use Tempest\Route\Response;

final readonly class PostController
{
    #[Post('/create-post')]
    public function store(PostRequest $request): Response
    {
        return Response::ok("{$request->title} {$request->text}");
    }
}