<?php

namespace Tempest\Testing;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class RunOn
{
    public function __construct(
        public string|UnitEnum $runner,
    ) {}
}