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

final readonly class AuthController
{
    public function __construct(
        private Authenticator $authenticator,
        private PasswordHasher $passwordHasher,
        private PreviousUrl $previousUrl,
    ) {}

    // TODO: Customise route paths to suit your application
    #[Get('/auth/login')]
    public function showLoginForm(): View
    {
        // TODO: implement, the code below is an example, and does not include a login form, customise to suit your application

        // HINTS & TIPS:
        // If using Tempest|Auth\OAuth your view would include links to login with your OAuth providers
        // i.e. <a href="/auth/generic">Login with Generic</a>
        // In an OAuth-only situation, you can remove the #[Post('/auth/login')] and public function login(..) entirely

        return view('./your.login.view.php');
    }

    // This method is not required if you are implementing an OAuth-only approach
    #[Post('/auth/login')]
    public function login(LoginRequest $request): Redirect
    {
        // TODO: implement, the code below is an example, customise to suit your application

        $user = query(User::class)
            ->select()
            ->where('email', $request->email)
            ->first();

        if (! $user || ! $this->passwordHasher->verify($request->password, $user->password)) {
            return new Redirect('/login')->flash('error', 'Invalid credentials');
        }

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
