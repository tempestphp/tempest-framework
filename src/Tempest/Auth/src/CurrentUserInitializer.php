<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;

final readonly class CurrentUserInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool
    {
        return $class->implements(CanAuthenticate::class);
    }

    public function initialize(ClassReflector $class, Container $container): object
    {
        $user = $container->get(Authenticator::class)->currentUser();

        if (! $user) {
            throw new CurrentUserNotLoggedIn();
        }

        return $user;
    }
}
