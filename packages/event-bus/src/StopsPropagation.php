<?php

namespace Tempest\EventBus;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class StopsPropagation
{
}
