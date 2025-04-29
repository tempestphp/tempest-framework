<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Unit\Fixtures;

use Tempest\Generation\Tests\Unit\Fixtures\SampleNamespace\SampleParameterAttribute;

final class ClassWithMethodParameterAttributes
{
    public function example(
        // @mago-expect best-practices/no-unused-parameter
        #[SampleParameterAttribute]
        string $parameter,
    ): void {
    }
}
