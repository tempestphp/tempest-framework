<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

use Tempest\Auth\Exceptions\AuthenticatableWasMissing;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

/**
 * Registers the currently-authenticated model in the container for injection.
 */
final readonly class CurrentAuthenticatableInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->implements(Authenticatable::class) || $class->is(Authenticatable::class);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        $authenticatable = $container->get(Authenticator::class)->current();

        if (! $authenticatable) {
            throw new AuthenticatableWasMissing();
        }

        return $authenticatable;
    }
}
