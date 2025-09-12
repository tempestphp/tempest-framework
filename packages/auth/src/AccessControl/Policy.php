<?php

namespace Tempest\Auth\AccessControl;

use Attribute;
use UnitEnum;

/**
 * When applied on a method, this attribute indicates that the method is a policy method for the specified resource.
 * The method must accept the resource instance as its first parameter and the subject as its second one.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Policy
{
    /**
     * @param class-string $resource A resource class that this policy applies to. If not specified, it will be inferred by the method's first argument.
     * @param null|UnitEnum|string|iterable<string|UnitEnum|null> $action An optional action that this policy applies to. If null, the policy applies to all actions for the resource.
     */
    public function __construct(
        public ?string $resource = null,
        public null|UnitEnum|string|iterable $action = null,
    ) {}
}
