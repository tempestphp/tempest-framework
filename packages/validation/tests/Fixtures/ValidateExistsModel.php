<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Database\Id;

/** @internal */
final class ValidateExistsModel
{
    public function __construct(
        public Id $id,
        public string $name,
    ) {}
}
