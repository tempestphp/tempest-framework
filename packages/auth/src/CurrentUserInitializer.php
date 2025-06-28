<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Tag;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final readonly class CurrentUserInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->implements(CanAuthenticate::class);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        $user = $container->get(Authenticator::class)->currentUser();

        if (! $user) {
            throw new AuthenticatedUserWasMissing();
        }

        return $user;
    }
}
