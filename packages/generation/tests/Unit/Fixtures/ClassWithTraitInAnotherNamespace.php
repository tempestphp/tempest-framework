<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Unit\Fixtures;

use Tempest\Generation\Tests\Unit\Fixtures\SampleNamespace\ExampleTrait;

final class ClassWithTraitInAnotherNamespace
{
    use ExampleTrait;
}
