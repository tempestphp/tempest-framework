<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;

#[Table('users_with_eager')]
final class UserWithEager
{
    use IsDatabaseModel;

    public function __construct(
        public PrimaryKey $id,
        public string $name,
        #[Eager]
        public ?ProfileWithEager $profile = null,
    ) {}
}
