<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

final readonly class RelationDefinition extends ModelDefinition
{
    public function __construct(
        /** @var class-string<\Tempest\Database\Model> $modelClass */
        protected string $modelClass,
        protected string $relationName,
        protected ModelDefinition|RelationDefinition $parentDefinition,
    ) {
    }

    public function getTableName(): TableName
    {
        $tableName = parent::getTableName()->tableName;

        $prefix = [];

        $definition = $this;

        while ($definition) {
            if ($definition instanceof RelationDefinition) {
                $prefix[] = $definition->relationName;
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
}
