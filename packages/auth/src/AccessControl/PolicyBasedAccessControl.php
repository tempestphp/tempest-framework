<?php

namespace Tempest\Auth\AccessControl;

use BackedEnum;
use Closure;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Auth\Exceptions\NoPolicyWereFoundForResource;
use Tempest\Auth\Exceptions\PolicyMethodIsInvalid;
use Tempest\Container\Container;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Support\Arr\ImmutableArray;
use UnitEnum;

final readonly class PolicyBasedAccessControl implements AccessControl
{
    public function __construct(
        private Container $container,
        private AuthConfig $authConfig,
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
        $policies = $this->findPoliciesForResourceAction($resource, $action);

        if ($policies->isEmpty()) {
            throw new NoPolicyWereFoundForResource($resource);
        }

        $resource = ! is_object($resource) ? null : $resource;

        foreach ($policies as $policy) {
            $decision = $this->evaluatePolicy($policy, $resource, $subject);

            if ($decision->granted === false) {
                return $decision;
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
     * @return ImmutableArray<int,MethodReflector>
     */
    private function findPoliciesForResourceAction(object|string $resource, UnitEnum|string $action): ImmutableArray
    {
        $resource = is_object($resource)
            ? $resource::class
            : $resource;

        $actionBeingEvaluated = match (true) {
            $action instanceof BackedEnum => $action->value,
            $action instanceof UnitEnum => $action->name,
            default => $action,
        };

        return new ImmutableArray($this->authConfig->policies[$resource] ?? [])
            ->filter(fn ($_, string $action) => $action === $actionBeingEvaluated)
            ->flatten();
    }

    private function evaluatePolicy(MethodReflector $policy, ?object $resource, ?object $subject = null): AccessDecision
    {
        $policyName = $this->getPolicyName($policy);

        $this->ensureParameterAcceptsInput(
            reflector: $policy->getParameter(key: 0),
            input: $resource,
            throw: fn (string $expected) => throw PolicyMethodIsInvalid::resourceParameterIsInvalid($policyName, $expected),
        );

        $this->ensureParameterAcceptsInput(
            reflector: $policy->getParameter(key: 1),
            input: $subject,
            throw: fn (string $expected) => throw PolicyMethodIsInvalid::subjectParameterIsInvalid($policyName, $expected),
        );

        $decision = $policy->invokeArgs(
            object: $this->container->get($policy->getDeclaringClass()->getName()),
            args: [$resource, $subject],
        );

        return AccessDecision::from($decision);
    }

    private function ensureParameterAcceptsInput(?ParameterReflector $reflector, mixed $input, \Closure $throw): void
    {
        if ($reflector === null || $input === null) {
            return;
        }

        if (! ($type = $reflector?->getType())) {
            return;
        }

        if ($type?->accepts($input)) {
            return;
        }

        $throw($type->getName());
    }

    private function getPolicyName(MethodReflector $policy): string
    {
        return sprintf('%s::%s', $policy->getDeclaringClass()->getName(), $policy->getName());
    }
}
