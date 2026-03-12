<?php

namespace Tests\Tempest\Integration\Auth\AccessControl;

use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\AuthConfig;
use Tempest\Reflection\ClassReflector;

/**
 * @phpstan-require-extends \Tests\Tempest\Integration\FrameworkIntegrationTestCase
 */
trait HasPolicyTests
{
    public function registerPoliciesFrom(string|object $class): self
    {
        $config = $this->container->get(AuthConfig::class);

        foreach (new ClassReflector($class)->getPublicMethods() as $method) {
            if ($policy = $method->getAttribute(Policy::class)) {
                $config->registerPolicy($method, $policy);
            }
        }

        return $this;
    }
}
