<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

use Tempest\Generation\Tests\Fixtures\SampleNamespace\SampleParameterAttribute;

final class ClassWithMethodParameterAttributes
{
    public function example(
        // @mago-expect best-practices/no-unused-parameter
        #[SampleParameterAttribute]
        string $parameter,
    ): void {
    }
}
