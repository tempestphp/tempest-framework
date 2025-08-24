<?php

namespace Tempest\Auth\AccessControl;

use UnitEnum;

/**
 * @template Subject of object
 * @template Resource of object
 */
interface AccessControl
{
    /**
     * Checks if the action is granted for the given resource and subject. If not, an exception is thrown.
     *
     * @template Resource of object
     * @param UnitEnum|string $action An arbitrary action to check access for, e.g. 'view', 'edit', etc.
     * @param Resource|class-string<Resource> $resource A model instance or class string of a model to check access for.
     * @param null|Subject $subject An optional subject to check access against, e.g. a user or service account.
     *
     * @throws AccessWasDenied
     */
    public function denyAccessUnlessGranted(UnitEnum|string $action, object|string $resource, ?object $subject = null): void;

    /**
     * Checks if the action is granted for the given resource and subject.
     *
     * @template Resource of object
     * @param UnitEnum|string $action An arbitrary action to check access for, e.g. 'view', 'edit', etc.
     * @param Resource|class-string<Resource> $resource A model instance or class string of a model to check access for.
     * @param null|Subject $subject An optional subject to check access against, e.g. a user or service account.
     */
    public function isGranted(UnitEnum|string $action, object|string $resource, ?object $subject = null): AccessDecision;
}
