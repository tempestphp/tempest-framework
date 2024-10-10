<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Relations\Fixtures;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;

final class HasOneParentModel implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName("has_one_parent_model");
    }


    #[HasOne]
    public HasOneRelatedModel $relatedModel;

    #[HasOne("otherParent")]
    public HasOneRelatedModel $otherRelatedModel;

    #[HasOne]
    public HasOneInvalidRelatedModel $inversePropertyNotFound;

    #[HasOne("non_existing_field")]
    public HasOneInvalidRelatedModel $inversePropertyMissing;

    #[HasOne("invalidType")]
    public HasOneInvalidRelatedModel $inversePropertyInvalid;
}
