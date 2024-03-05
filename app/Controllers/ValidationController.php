<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Http\Post;

final readonly class ValidationController
{
    #[Post('/test-validation-responses')]
    public function __invoke(RequestForValidationController $request)
    {
    }
}
