<?php

namespace Tempest\Auth\AccessControl;

use Attribute;
use UnitEnum;

/**
 * When applied on a method, this attribute indicates that the method is a policy method for the specified resource.
 * The method must accept an action as its first parameter, the resource instance as its second, and the subject as its last.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class PolicyFor
{
    /**
     * @param class-string $resource A resource class that this policy applies to.
     * @param null|UnitEnum|string|iterable<string|UnitEnum|null> $action An optional action that this policy applies to. If null, the policy applies to all actions for the resource.
     */
    public function __construct(
        public string $resource,
        public null|UnitEnum|string|iterable $action = null,
    ) {}
}
