<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('has_one_parent_model')]
final class HasOneParentModel
{
    use IsDatabaseModel;

    #[HasOne]
    public HasOneRelatedModel $relatedModel;

    #[HasOne('otherParent')]
    public HasOneRelatedModel $otherRelatedModel;

    #[HasOne]
    public HasOneInvalidRelatedModel $inversePropertyNotFound;

    #[HasOne('non_existing_field')]
    public HasOneInvalidRelatedModel $inversePropertyMissing;

    #[HasOne('invalidType')]
    public HasOneInvalidRelatedModel $inversePropertyInvalid;
}
