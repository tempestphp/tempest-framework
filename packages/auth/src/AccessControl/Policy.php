<?php

namespace Tempest\Auth\AccessControl;

use Tempest\Reflection\ClassReflector;
use UnitEnum;

/**
 * @template T of object
 */
interface Policy
{
    /**
     * @var class-string<T>
     */
    public string $model {
        get;
    }

    /**
     * Checks if the action is granted on the given resource for the given subject.
     *
     * @param null|T $resource
     */
    public function check(UnitEnum|string $action, ?object $resource, ?object $subject): bool|AccessDecision;
}
