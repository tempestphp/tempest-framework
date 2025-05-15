<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\HasMany;
use Tempest\Database\Table;

#[Table('relation')]
final class RelationModel
{
    /** @var \Tests\Tempest\Integration\Database\Fixtures\OwnerModel[] */
    public array $owners = [];

    /** @var \Tests\Tempest\Integration\Database\Fixtures\OwnerModel[] */
    #[HasMany(ownerJoin: 'overwritten_id')]
    public array $ownerJoinField = [];

    /** @var \Tests\Tempest\Integration\Database\Fixtures\OwnerModel[] */
    #[HasMany(ownerJoin: 'overwritten.overwritten_id')]
    public array $ownerJoinFieldAndTable = [];

    /** @var \Tests\Tempest\Integration\Database\Fixtures\OwnerModel[] */
    #[HasMany(relationJoin: 'overwritten_id')]
    public array $relationJoinField = [];

    /** @var \Tests\Tempest\Integration\Database\Fixtures\OwnerModel[] */
    #[HasMany(relationJoin: 'overwritten.overwritten_id')]
    public array $relationJoinFieldAndTable = [];

    public string $name;
}
