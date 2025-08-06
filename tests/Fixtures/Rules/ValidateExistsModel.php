<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Rules;

use Tempest\Database\Id;

/** @internal */
final class ValidateExistsModel
{
    public function __construct(
        public Id $id,
        public string $name,
    ) {}
}
