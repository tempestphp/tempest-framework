<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class C implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {
    }
}
