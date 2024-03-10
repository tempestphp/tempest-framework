<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Response;
use function Tempest\response;
use function Tempest\uri;

final readonly class ValidationController
{
    #[Get('/test-validation-responses')]
    public function get(): Response
    {
        return response()->ok();
    }

    #[Post('/test-validation-responses')]
    public function store(RequestForValidationController $request): Response
    {
        return response()->redirect(uri([self::class, 'get']));
    }
}
