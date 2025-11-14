<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final readonly class OptionalParametersController
{
    #[Get('/articles/{id}/{?slug}')]
    public function articles(string $id, ?string $slug = null): Response
    {
        if ($slug === null) {
            return new Ok("Article {$id} without slug");
        }

        return new Ok("Article {$id} with slug {$slug}");
    }

    #[Get('/users/{?id}')]
    public function users(?string $id = null): Response
    {
        if ($id === null) {
            return new Ok('All users');
        }

        return new Ok("User {$id}");
    }

    #[Get('/posts/{?id}/{?category}')]
    public function posts(?string $id = null, ?string $category = null): Response
    {
        if ($id === null && $category === null) {
            return new Ok('All posts');
        }

        if ($category === null) {
            return new Ok("Post {$id}");
        }

        return new Ok("Post {$id} in category {$category}");
    }
}
