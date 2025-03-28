<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('belongs_to_related')]
final class BelongsToRelatedModel
{
    use IsDatabaseModel;

    /** @var \Tests\Tempest\Integration\Database\Relations\Fixtures\BelongsToParentModel[] */
    public array $inferred = [];

    #[HasMany('other_id')]
    /** @var \Tests\Tempest\Integration\Database\Relations\Fixtures\BelongsToParentModel[] */
    public array $attribute = [];

    #[HasMany('other_id', BelongsToParentModel::class, 'other_id')]
    public array $full = [];

    /** @var \Tests\Tempest\Integration\Database\Relations\Fixtures\HasOneParentModel[] */
    public array $invalid = [];
}
