<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Php\Fixtures;

use Tempest\Generation\Tests\Php\Fixtures\SampleNamespace\ExampleTrait;

final class ClassWithTraitInAnotherNamespace
{
    use ExampleTrait;
}
