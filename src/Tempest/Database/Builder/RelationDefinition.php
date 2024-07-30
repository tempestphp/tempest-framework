<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

final readonly class RelationDefinition
{
    public function __construct(
        /** @var class-string<\Tempest\Database\Model> $modelClass */
        protected string $modelClass,
        protected string $relationName,
        protected ModelDefinition|RelationDefinition $parentDefinition,
        protected InverseRelationDefinition|null $inverseDefinition,
        protected RelationType $type,
    ) {
    }

    public function getTableName(): TableName
    {
//        $tableName = parent::getTableName()->tableName;

        $prefix = [];

        $definition = $this;

        while ($definition) {
            if ($definition instanceof RelationDefinition) {
                $prefix[] = match($definition->type) {
                    RelationType::HAS_MANY => $definition->relationName . '[]',
                    default => $definition->relationName,
                };

                $definition = $definition->parentDefinition;
            } else {
                $prefix[] = $definition->getTableName()->tableName;
                $definition = null;
            }
        }

        $prefix = implode('.', array_reverse($prefix));

        return new TableName($tableName, $prefix);
    }

    public function getRelationName(): string
    {
        return $this->relationName;
    }

    public function getRelationFieldName(): FieldName
    {
        return $this->parentDefinition->getFieldName($this->relationName . '_id');
    }

    public function getJoinStatement(): string
    {
        return match($this->type) {
            RelationType::HAS_MANY => $this->getHasManyJoinStatement(),
            RelationType::BELONGS_TO => $this->getBelongsToJoinStatement(),
        };
    }

    private function getBelongsToJoinStatement(): string
    {
        return sprintf(
            'INNER JOIN %s ON %s = %s',
            $this->getTableName(),
            $this->getFieldName('id'),
            $this->getRelationFieldName(),
        );
    }

    private function getHasManyJoinStatement(): string
    {
        return sprintf(
            'INNER JOIN %s ON %s = %s',
            $this->getTableName(),
            $this->inverseDefinition->getFieldName(),
            $this->parentDefinition->getFieldName('id'),
        );
    }
}
