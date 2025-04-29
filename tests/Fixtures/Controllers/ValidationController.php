<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;

use function Tempest\uri;

final readonly class ValidationController
{
    #[Get('/test-validation-responses')]
    public function get(): Response
    {
        return new Ok();
    }

    #[Post('/test-validation-responses')]
    public function store(RequestForValidationController $request): Response // @mago-expect best-practices/no-unused-parameter
    {
        return new Redirect(uri([self::class, 'get']));
    }
}
