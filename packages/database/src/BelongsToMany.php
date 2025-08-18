<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;
use function Tempest\Support\str;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BelongsToMany implements Relation
{
    public PropertyReflector $property;

    public string $name {
        get => $this->property->getName();
    }

    private ?string $parent = null;

    public function __construct(
        private readonly ?string $pivotTable = null,
        private readonly ?string $pivotCurrentKey = null,
        private readonly ?string $pivotRelatedKey = null,
        private readonly array $pivotFields = [],
    ) {}

    public function setParent(string $name): self
    {
        $this->parent = $name;

        return $this;
    }

    public function getSelectFields(): ImmutableArray
    {
        $relatedModel = inspect($this->property->getIterableType()->asClass());

        $fields = $relatedModel
            ->getSelectFields()
            ->map(fn ($field) => new FieldStatement(
                $relatedModel->getTableName() . '.' . $field,
            )
                ->withAlias(
                    sprintf('%s.%s', $this->property->getName(), $field),
                )
                ->withAliasPrefix($this->parent));

        if ($this->pivotFields) {
            $pivotTable = $this->getPivotTableName(
                inspect($this->property->getClass()),
                $relatedModel,
            );

            foreach ($this->pivotFields as $pivotField) {
                $fields[] = new FieldStatement(
                    sprintf('%s.%s', $pivotTable, $pivotField),
                )
                    ->withAlias(
                        sprintf('%s.pivot.%s', $this->property->getName(), $pivotField),
                    )
                    ->withAliasPrefix($this->parent);
            }
        }

        return arr($fields);
    }

    public function primaryKey(): string
    {
        $relatedModel = inspect($this->property->getIterableType()->asClass());
        $primaryKey = $relatedModel->getPrimaryKey();

        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relatedModel->getName(), 'BelongsToMany');
        }

        return $primaryKey;
    }

    public function idField(): string
    {
        return sprintf(
            '%s.%s',
            $this->property->getName(),
            $this->primaryKey(),
        );
    }

    public function getJoinStatement(): JoinStatement
    {
        $currentModel = inspect($this->property->getClass());
        $relatedModel = inspect($this->property->getIterableType()->asClass());

        $pivotTable = $this->getPivotTableName($currentModel, $relatedModel);

        $currentPrimaryKey = $currentModel->getPrimaryKey();
        if ($currentPrimaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($currentModel->getName(), 'BelongsToMany');
        }

        $relatedPrimaryKey = $relatedModel->getPrimaryKey();
        if ($relatedPrimaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relatedModel->getName(), 'BelongsToMany');
        }

        $pivotCurrentKey = $this->getPivotCurrentKey($currentModel, $pivotTable);
        $pivotRelatedKey = $this->getPivotRelatedKey($relatedModel, $pivotTable);

        return new JoinStatement(sprintf(
            'LEFT JOIN %s ON %s.%s = %s.%s LEFT JOIN %s ON %s.%s = %s.%s',
            $pivotTable,
            $pivotTable,
            $pivotCurrentKey,
            $currentModel->getTableName(),
            $currentPrimaryKey,
            $relatedModel->getTableName(),
            $relatedModel->getTableName(),
            $relatedPrimaryKey,
            $pivotTable,
            $pivotRelatedKey,
        ));
    }

    private function getPivotTableName(ModelInspector $currentModel, ModelInspector $relatedModel): string
    {
        if ($this->pivotTable !== null) {
            return $this->pivotTable;
        }

        // Default: alphabetical order of singularized table names
        $currentSingular = str($currentModel->getTableName())->singularizeLastWord()->toString();
        $relatedSingular = str($relatedModel->getTableName())->singularizeLastWord()->toString();

        $tables = [$currentSingular, $relatedSingular];
        sort($tables);

        return implode('_', $tables);
    }

    private function getPivotCurrentKey(ModelInspector $currentModel, string $pivotTable): string
    {
        if ($this->pivotCurrentKey !== null) {
            return $this->qualifyPivotKey($this->pivotCurrentKey, $pivotTable);
        }

        $primaryKey = $currentModel->getPrimaryKey();
        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($currentModel->getName(), 'BelongsToMany');
        }

        return str($currentModel->getTableName())->singularizeLastWord() . '_' . $primaryKey;
    }

    private function getPivotRelatedKey(ModelInspector $relatedModel, string $pivotTable): string
    {
        if ($this->pivotRelatedKey !== null) {
            return $this->qualifyPivotKey($this->pivotRelatedKey, $pivotTable);
        }

        $primaryKey = $relatedModel->getPrimaryKey();
        if ($primaryKey === null) {
            throw ModelDidNotHavePrimaryColumn::neededForRelation($relatedModel->getName(), 'BelongsToMany');
        }

        return str($relatedModel->getTableName())->singularizeLastWord() . '_' . $primaryKey;
    }

    private function qualifyPivotKey(string $key, string $pivotTable): string
    {
        if (! strpos($key, '.')) {
            return $key;
        }

        return sprintf('%s.%s', $pivotTable, $key);
    }
}
