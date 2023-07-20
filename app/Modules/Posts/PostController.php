<?php

declare(strict_types=1);

namespace App\Modules\Posts;

use Tempest\Http\GenericResponse;
use Tempest\Http\Post;

use function Tempest\response;

final readonly class PostController
{
    #[Post('/create-post')]
    public function store(PostRequest $request): GenericResponse
    {
        return response("{$request->title} {$request->text}");
    }
}
