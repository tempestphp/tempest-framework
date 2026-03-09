<?php

namespace Tempest\Auth\AccessControl;

use Tempest\Auth\Exceptions\AccessWasDenied;
use UnitEnum;

/**
 * @template TSubject of object
 * @template TResource of object
 */
interface AccessControl
{
    /**
     * Checks if the action is granted for the given resource and subject. If not, an exception is thrown.
     *
     * @param UnitEnum|string $action An arbitrary action to check access for, e.g. 'view', 'edit', etc.
     * @param TResource|class-string<TResource> $resource A model instance or class string of a model to check access for.
     * @param null|TSubject $subject An optional subject to check access against, e.g. a user or service account.
     *
     * @throws AccessWasDenied
     */
    public function ensureGranted(UnitEnum|string $action, object|string $resource, ?object $subject = null): void;

    /**
     * Checks if the action is granted for the given resource and subject.
     *
     * @param UnitEnum|string $action An arbitrary action to check access for, e.g. 'view', 'edit', etc.
     * @param TResource|class-string<TResource> $resource A model instance or class string of a model to check access for.
     * @param null|TSubject $subject An optional subject to check access against, e.g. a user or service account.
     */
    public function isGranted(UnitEnum|string $action, object|string $resource, ?object $subject = null): AccessDecision;
}
