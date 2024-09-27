<?php

namespace Tempest\Auth;

use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Validation\Validator;
use Tempest\View\View;
use function Tempest\view;

final readonly class AuthController
{
    public function __construct(
        private Authenticator $authenticator,
        private Validator $validator,
    ) {}

    #[Get('/login')]
    public function login(): View
    {
        return view(__DIR__ . '/login.view.php')->data(
            user: $this->authenticator->currentUser(),
        );
    }

    #[Post('/login')]
    public function attemptLogin(LoginRequest $loginRequest): Response
    {
        $this->authenticator->login($loginRequest->getUser());

        return new Redirect('/');
    }

    #[Get('/logout')]
    public function logout(): Response
    {
        $this->authenticator->logout();

        return new Redirect('/');
    }
}