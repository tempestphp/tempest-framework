<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures\SampleNamespace;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class SampleParameterAttribute
{
}
