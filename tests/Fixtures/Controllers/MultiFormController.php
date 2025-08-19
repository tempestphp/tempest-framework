<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Post;
use Tests\Tempest\Fixtures\Requests\LoginRequest;
use Tests\Tempest\Fixtures\Requests\RegisterRequest;
use Tests\Tempest\Fixtures\Requests\ValidationRequest;

final readonly class MultiFormController
{
    #[Post('/multi-form/default')]
    public function defaultForm(ValidationRequest $request): Response
    {
        return new Ok('Success');
    }

    #[Post('/multi-form/login')]
    public function login(LoginRequest $_request): Response
    {
        return new Ok('Logged in');
    }

    #[Post('/multi-form/register')]
    public function register(RegisterRequest $_request): Response
    {
        return new Ok('Registered');
    }
}
