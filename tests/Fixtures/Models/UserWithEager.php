<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Eager;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;

#[Table('users')]
final class UserWithEager
{
    use IsDatabaseModel;

    public function __construct(
        public PrimaryKey $id,
        public string $name,
        #[Eager]
        #[HasOne]
        public ?ProfileWithEager $profile = null,
    ) {}
}
