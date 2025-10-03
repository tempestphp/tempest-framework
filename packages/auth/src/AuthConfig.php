<?php

declare(strict_types=1);

namespace Tempest\Auth;

use BackedEnum;
use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Exceptions\PolicyWasInvalid;
use Tempest\Reflection\MethodReflector;
use Tempest\Support\Arr;
use Tempest\Support\Str;
use UnitEnum;

final class AuthConfig
{
    /**
     * @param array<class-string<Authenticatable>> $authenticatables
     * @param array<class-string,array<string,MethodReflector[]>> $policies
     */
    public function __construct(
        public array $authenticatables = [],
        public array $policies = [],
    ) {}

    public function registerPolicy(MethodReflector $handler, Policy $policy): self
    {
        if (! $policy->resource) {
            $policy->resource = $handler->getParameter(key: 0)?->getType()?->getName();
        }

        if (! $policy->resource) {
            throw PolicyWasInvalid::resourceCouldNotBeInferred(
                policyName: sprintf('%s::%s', $handler->getDeclaringClass()->getName(), $handler->getName()),
            );
        }

        $this->policies[$policy->resource] ??= [];

        if ($policy->action === null) {
            $policy->action = Str\to_kebab_case($handler->getName());
        }

        foreach (Arr\wrap($policy->action) as $action) {
            $action = Str\parse($action);

            $this->policies[$policy->resource][$action] ??= [];
            $this->policies[$policy->resource][$action][] = $handler;
        }

        return $this;
    }
}
