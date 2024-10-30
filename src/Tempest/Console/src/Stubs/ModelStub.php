<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Validation\Rules\Length;

final class ModelStub
{
    public function __construct(
        #[Length(min: 1, max: 120)]
        public string $title
    ) {
    }
}
