<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final readonly class InferredConstraintsController
{
    #[Get('/inferred/int/{id}')]
    public function withInt(int $id): Response
    {
        return new Ok("int: {$id}");
    }

    #[Get('/inferred/string/{name}')]
    public function withString(string $name): Response
    {
        return new Ok("string: {$name}");
    }

    #[Get('/inferred/float/{price}')]
    public function withFloat(float $price): Response
    {
        return new Ok("float: {$price}");
    }

    #[Get('/inferred/optional-int/{?id}')]
    public function withOptionalInt(?int $id = null): Response
    {
        return new Ok($id !== null ? "int: {$id}" : 'no id');
    }

    #[Get('/inferred/explicit/{id:\d{3}}')]
    public function withExplicitConstraint(int $id): Response
    {
        return new Ok("explicit: {$id}");
    }

    #[Get('/inferred/mixed/{id}/{name}')]
    public function withMixedTypes(int $id, string $name): Response
    {
        return new Ok("id: {$id}, name: {$name}");
    }
}
