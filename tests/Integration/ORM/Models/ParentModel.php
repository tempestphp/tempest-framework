<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('parent')]
final class ParentModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,

        /** @var \Tests\Tempest\Integration\ORM\Models\ThroughModel[] */
        public array $through = [],
    ) {}
}
