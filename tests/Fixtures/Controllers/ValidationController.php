<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;
use Tempest\Router\Responses\Redirect;

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
