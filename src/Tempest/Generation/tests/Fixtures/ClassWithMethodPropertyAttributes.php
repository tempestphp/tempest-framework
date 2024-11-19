<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

use Tempest\Generation\Tests\Fixtures\SampleNamespace\SamplePropertyAttribute;

final class ClassWithMethodPropertyAttributes
{
    public function example(
        #[SamplePropertyAttribute]
        string $parameter
    ): void {}
}
