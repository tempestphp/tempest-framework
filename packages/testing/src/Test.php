<?php

namespace Tempest\Testing;

use Attribute;
use Tempest\Reflection\MethodReflector;

#[Attribute(Attribute::TARGET_METHOD)]
final class Test
{
}