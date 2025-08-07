<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;

#[Table('b')]
final class B
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public C $c,
    ) {}
}
