<?php

namespace Tempest\Auth\AccessControl;

use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Auth\Exceptions\NoPolicyWereFoundForResource;
use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;
use UnitEnum;

use function Tempest\Support\arr;

final class PolicyBasedAccessControl implements AccessControl
{
    public function __construct(
        private readonly Container $container,
        private readonly AuthConfig $authConfig,
    ) {}

    public function denyAccessUnlessGranted(UnitEnum|string $action, object|string $resource, ?object $subject = null): void
    {
        $result = $this->isGranted($action, $resource, $subject);

        if ($result->granted === false) {
            throw new AccessWasDenied($result);
        }
    }

    public function isGranted(UnitEnum|string $action, object|string $resource, ?object $subject = null): AccessDecision
    {
        $subject = $this->resolveSubject($subject);
        $policies = $this->findPoliciesForResource($resource);

        if ($policies->isEmpty()) {
            throw new NoPolicyWereFoundForResource($resource);
        }

        $resource = ! is_object($resource) ? null : $resource;

        foreach ($policies as $policy) {
            $result = AccessDecision::from($policy->check($action, $resource, $subject));

            if ($result->granted === false) {
                return $result;
            }
        }

        return AccessDecision::granted();
    }

    private function resolveSubject(?object $subject): ?object
    {
        if ($subject) {
            return $subject;
        }

        try {
            return $this->container->get(Authenticator::class)->current();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @template T of object
     * @param T|class-string<T> $resource
     * @return ImmutableArray<int,Policy<T>>
     */
    private function findPoliciesForResource(object|string $resource): ImmutableArray
    {
        $resource = is_object($resource) ? $resource::class : $resource;

        return arr($this->authConfig->policies)
            ->map(fn (string $policy) => $this->container->get($policy))
            ->filter(fn (Policy $policy) => $policy->model === $resource);
    }
}
