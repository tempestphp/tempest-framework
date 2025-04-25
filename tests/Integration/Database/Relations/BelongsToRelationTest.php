<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Database\Builder\ModelDefinition;
use Tests\Tempest\Integration\Database\Relations\Fixtures\BelongsToParentModel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class BelongsToRelationTest extends FrameworkIntegrationTestCase
{
    public function test_inferred_belongs_to_relation(): void
    {
        $definition = new ModelDefinition(BelongsToParentModel::class);
        $inferredRelation = $definition->getRelations('relatedModel');

        $this->assertCount(1, $inferredRelation);
        $this->assertSame('belongs_to_parent_model.relatedModel', $inferredRelation[0]->getRelationName());
        $this->assertEquals(
            'LEFT JOIN `belongs_to_related` AS `belongs_to_parent_model.relatedModel`' .
                ' ON `belongs_to_parent_model`.`relatedModel_id` = `belongs_to_parent_model.relatedModel`.`id`',
            $inferredRelation[0]->getStatement(),
        );
    }

    public function test_attribute_with_default_belongs_to_relation(): void
    {
        $definition = new ModelDefinition(BelongsToParentModel::class);
        $namedRelation = $definition->getRelations('otherRelatedModel');

        $this->assertCount(1, $namedRelation);

        $this->assertSame('belongs_to_parent_model.otherRelatedModel', $namedRelation[0]->getRelationName());
        $this->assertEquals(
            'LEFT JOIN `belongs_to_related` AS `belongs_to_parent_model.otherRelatedModel`' .
                ' ON `belongs_to_parent_model`.`other_id` = `belongs_to_parent_model.otherRelatedModel`.`id`',
            $namedRelation[0]->getStatement(),
        );
    }

    public function test_attribute_belongs_to_relation(): void
    {
        $definition = new ModelDefinition(BelongsToParentModel::class);
        $doublyNamedRelation = $definition->getRelations('stillOtherRelatedModel');

        $this->assertCount(1, $doublyNamedRelation);

        $this->assertSame('belongs_to_parent_model.stillOtherRelatedModel', $doublyNamedRelation[0]->getRelationName());
        $this->assertEquals(
            'LEFT JOIN `belongs_to_related` AS `belongs_to_parent_model.stillOtherRelatedModel`' .
                ' ON `belongs_to_parent_model`.`other_id` = `belongs_to_parent_model.stillOtherRelatedModel`.`other_id`',
            $doublyNamedRelation[0]->getStatement(),
        );
    }
}
