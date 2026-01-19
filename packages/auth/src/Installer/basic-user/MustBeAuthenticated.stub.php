<?php

namespace Tempest\Auth\Installer;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Core\Priority;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
#[Priority(Priority::HIGHEST)]
final readonly class MustBeAuthenticated implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
        private PreviousUrl $previousUrl,
    ) {}

    // TODO: implement, the code below is an example, customise to suit your application

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        // Check if user is authenticated
        $user = $this->authenticator->current();

        if ($user === null) {
            // Store the intended URL
            $this->previousUrl->setIntended($request->path);

            // Redirect to login if not authenticated
            return new Redirect('/auth/login')
                ->flash('error', 'You must be logged in to access this page');
        }

        // User is authenticated, continue with request
        return $next($request);
    }
}
