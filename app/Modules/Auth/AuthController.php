<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Modules\Auth\Models\User;
use Tempest\Auth\Authenticator;
use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Request;
use Tempest\Http\Response;
use function Tempest\response;

final readonly class AuthController
{
    public function __construct(
        private Authenticator $authenticator,
    ) {
    }

    #[Post('/login')]
    public function login(Request $request): Response
    {
        $user = (new User(name: 'John Doe'))->setCredentials(
            identifier: $request->get('email'),
            secret: $request->get('password')
        );

        $this->authenticator->login($user);

        return response()->ok();
    }

    #[Post('/logout')]
    public function logout(): Response
    {
        $this->authenticator->logout();

        return response()->ok();
    }

    #[Get('/me')]
    public function user(): Response
    {
        return response()
            ->ok()
            ->addHeader('Content-Type', 'application/json')
            ->setBody(json_encode($this->authenticator->user()));
    }
}
