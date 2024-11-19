<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

use Tempest\Generation\Tests\Fixtures\SampleNamespace\ExampleTrait;

final class ClassWithTraitInAnotherNamespace
{
    use ExampleTrait;
}
