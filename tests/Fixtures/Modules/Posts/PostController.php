<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Posts;

use Tempest\Router\Post;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;

final readonly class PostController
{
    #[Post('/create-post')]
    public function store(PostRequest $request): Response
    {
        return new Ok("{$request->title} {$request->text}");
    }
}
