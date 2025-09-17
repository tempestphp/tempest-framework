<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

use Tempest\Generation\Tests\Fixtures\SampleNamespace\SampleParameterAttribute;

final class ClassWithMethodParameterAttributes
{
    public function example(
        #[SampleParameterAttribute]
        string $parameter,
    ): void {
    }
}
