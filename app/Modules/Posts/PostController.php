<?php

declare(strict_types=1);

namespace App\Modules\Posts;

use Tempest\Http\Post;

use Tempest\Http\Response;
use function Tempest\response;

final readonly class PostController
{
    #[Post('/create-post')]
    public function store(PostRequest $request): Response
    {
        return response("{$request->title} {$request->text}");
    }
}
