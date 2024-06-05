<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Fixtures;

use Tempest\Validation\Rules\Length;

class ObjectToBeValidated
{
    public function __construct(
        #[Length(min: 2, max: 3)]
        public string $name,
    ) {
    }
}
