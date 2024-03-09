<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Post;
use function Tempest\response;

final readonly class ValidationController
{
    #[Get('/test-validation-responses')]
    public function get()
    {
        return response()->ok();
    }

    #[Post('/test-validation-responses')]
    public function store(RequestForValidationController $request)
    {
    }
}
