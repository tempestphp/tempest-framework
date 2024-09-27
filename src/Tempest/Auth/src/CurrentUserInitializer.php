<?php

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class CurrentUserInitializer implements Initializer
{
    public function initialize(Container $container): User
    {
        $user = $container->get(Authenticator::class)->currentUser();

        if (! $user) {
            throw new CurrentUserNotLoggedIn();
        }

        return $user;
    }
}