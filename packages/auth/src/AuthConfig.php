<?php

declare(strict_types=1);

namespace Tempest\Auth;

use BackedEnum;
use Tempest\Auth\AccessControl\PolicyFor;
use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Reflection\MethodReflector;
use Tempest\Support\Arr;
use Tempest\Support\Str;
use UnitEnum;

final class AuthConfig
{
    /**
     * @param null|class-string<CanAuthenticate> $authenticatable
     * @param array<class-string,array<string,MethodReflector[]>> $policies
     */
    public function __construct(
        public ?string $authenticatable = null,
        public array $policies = [],
    ) {}

    public function registerPolicy(MethodReflector $handler, PolicyFor $policy): self
    {
        $this->policies[$policy->resource] ??= [];

        if ($policy->action === null) {
            $policy->action = Str\to_kebab_case($handler->getName());
        }

        foreach (Arr\wrap($policy->action) as $action) {
            $action = match (true) {
                $action instanceof BackedEnum => $action->value,
                $action instanceof UnitEnum => $action->name,
                default => $action,
            };

            $this->policies[$policy->resource][$action] ??= [];
            $this->policies[$policy->resource][$action][] = $handler;
        }

        return $this;
    }
}
