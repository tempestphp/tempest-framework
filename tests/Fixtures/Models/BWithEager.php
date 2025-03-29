<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('b')]
final class BWithEager
{
    use IsDatabaseModel;

    public function __construct(
        #[Eager]
        public C $c,
    ) {}
}
