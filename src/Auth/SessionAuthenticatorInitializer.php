<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Exceptions\MissingIdentifiableException;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class SessionAuthenticatorInitializer implements Initializer
{
    /**
     * @throws MissingIdentifiableException
     */
    public function initialize(Container $container): Authenticator
    {
        $authConfig = $container->get(AuthConfig::class);
        if (is_null($authConfig->identifiable)) {
            throw new MissingIdentifiableException();
        }

        return $container->get($authConfig->authenticator);
    }
}
