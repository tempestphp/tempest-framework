<?php

namespace Tempest\Auth\Installer;

use App\Authentication\User;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cryptography\Password\PasswordHasher;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;

use function Tempest\Database\query;
use function Tempest\View\view;

final readonly class LoginController
{
    public function __construct(
        private Authenticator $authenticator,
        private PasswordHasher $passwordHasher,
        private PreviousUrl $previousUrl,
    ) {}

    #[Get('/auth/login')]
    public function showLoginForm(): View
    {
        // TODO: implement, the code below is an example, and does not include a login form, customise to suit your application

        return view('./your.login.view.php');
    }

    // This method is not required if you are implementing an OAuth-only approach
    #[Post('/auth/login')]
    public function login(LoginRequest $request): Redirect
    {
        // TODO: implement, the code below is an example, customise to suit your application
    
        // Database query here to check for your user //

        $this->authenticator->authenticate($user);

        // Get the intended URL and redirect there, or default to home
        // getIntended() automatically consumes/removes the stored URL
        $intendedUrl = $this->previousUrl->getIntended('/dashboard');

        return new Redirect($intendedUrl)
            ->flash('success', 'Logged in successfully');
    }

    #[Post('/auth/logout')]
    public function logout(): Redirect
    {
        $this->authenticator->deauthenticate();

        return new Redirect('/')
            ->flash('success', 'You have been logged out');
    }
}
