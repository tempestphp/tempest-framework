<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;

#[Table('profiles_with_eager')]
final class ProfileWithEager
{
    use IsDatabaseModel;

    public function __construct(
        public PrimaryKey $id,
        public string $bio,
        #[Eager]
        public ?UserWithEager $user = null,
    ) {}
}
