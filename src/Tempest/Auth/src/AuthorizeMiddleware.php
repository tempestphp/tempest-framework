<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Http\HttpMiddleware;
use Tempest\Http\MatchedRoute;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;

final readonly class AuthorizeMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
        private MatchedRoute $matchedRoute,
        private Container $container,
    ) {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        $attribute = $this->matchedRoute
            ->route
            ->handler
            ->getAttribute(Allow::class);

        if ($attribute === null) {
            return $next($request);
        }

        $user = $this->authenticator->currentUser();

        if (! $user instanceof CanAuthorize) {
            return new Forbidden();
        }

        $permission = $attribute->permission;

        if (is_a($permission, Authorizer::class, true)) {
            /** @var class-string<\Tempest\Auth\Authorizer> $permission */
            /** @var Authorizer $authorizer */
            $authorizer = $this->container->get($permission);

            $isAllowed = $authorizer->authorize($user);
        } else {
            $isAllowed = $user->hasPermission($permission);
        }

        if (! $isAllowed) {
            return new Forbidden();
        }

        return $next($request);
    }
}
