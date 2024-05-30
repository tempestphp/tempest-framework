<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use function Tempest\uri;

final readonly class ValidationController
{
    #[Get('/test-validation-responses')]
    public function get(): Response
    {
        return new Ok();
    }

    #[Post('/test-validation-responses')]
    public function store(RequestForValidationController $request): Response
    {
        return new Redirect(uri([self::class, 'get']));
    }
}
