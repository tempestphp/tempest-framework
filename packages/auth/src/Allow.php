<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Allow
{
    public function __construct(
        /** @var string|UnitEnum|class-string<\Tempest\Auth\Authorizer> $permission */
        public string|UnitEnum $permission,
    ) {}
}
