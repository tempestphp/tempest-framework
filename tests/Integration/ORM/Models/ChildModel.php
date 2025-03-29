<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('child')]
final class ChildModel
{
    use IsDatabaseModel;

    #[HasOne]
    public ThroughModel $through;

    #[HasOne('child2')]
    public ThroughModel $through2;

    public function __construct(
        public string $name,
    ) {}
}
