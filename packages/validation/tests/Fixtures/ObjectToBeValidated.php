<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\Rules\HasLength;

final class ObjectToBeValidated
{
    public function __construct(
        #[HasLength(min: 2, max: 3)]
        public string $name,
    ) {}
}
