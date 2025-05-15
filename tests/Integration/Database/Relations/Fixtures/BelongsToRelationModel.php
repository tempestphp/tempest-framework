<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('relation')]
final class BelongsToRelationModel
{
    use IsDatabaseModel;

    /** @var \Tests\Tempest\Integration\Database\Relations\Fixtures\BelongsToOwnerModel[] */
    public array $inferred = [];

    #[HasMany('other_id')]
    /** @var \Tests\Tempest\Integration\Database\Relations\Fixtures\BelongsToOwnerModel[] */
    public array $attribute = [];

    #[HasMany('other_id', BelongsToOwnerModel::class, 'other_id')]
    public array $full = [];

    /** @var \Tests\Tempest\Integration\Database\Relations\Fixtures\HasOneParentModel[] */
    public array $invalid = [];
}
