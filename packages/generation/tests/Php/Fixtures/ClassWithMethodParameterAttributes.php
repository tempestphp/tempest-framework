<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Php\Fixtures;

use Tempest\Generation\Tests\Php\Fixtures\SampleNamespace\SampleParameterAttribute;

final class ClassWithMethodParameterAttributes
{
    public function example(
        #[SampleParameterAttribute]
        string $parameter,
    ): void {
    }
}
