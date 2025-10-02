<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\BelongsTo;
use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;

#[Table('profiles')]
final class ProfileWithEager
{
    use IsDatabaseModel;

    public function __construct(
        public PrimaryKey $id,
        public string $bio,
        public ?int $user_id,
        #[Eager]
        #[BelongsTo]
        public ?UserWithEager $user = null,
    ) {}
}
