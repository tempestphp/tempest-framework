<?php

namespace Tempest\Database\Mappers;

use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Mapper\Mapper;

use function Tempest\Database\model;
use function Tempest\map;
use function Tempest\Support\arr;

#[SkipDiscovery]
final class SelectModelMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): array
    {
        $model = model($to);

        $idField = $model->getPrimaryField();

        $parsed = arr($from)
            ->groupBy(function (array $data) use ($idField) {
                return $data[$idField];
            })
            ->map(fn (array $rows) => $this->normalizeFields($model, $rows));

        return map($parsed->values()->toArray())->collection()->to($to);
    }

    private function normalizeFields(ModelInspector $model, array $rows): array
    {
        $data = [];

        $mainTable = $model->getTableDefinition()->name;

        $hasManyRelations = [];

        foreach ($rows as $row) {
            foreach ($row as $field => $value) {
                $parts = explode('.', $field);

                $mainField = $parts[0];

                // Main fields
                if ($mainField === $mainTable) {
                    $data[$parts[1]] = $value;
                    continue;
                }

                $relation = $model->getRelation($parts[0]);

                // IF count > 2

                // BelongsTo
                if ($relation instanceof BelongsTo || $relation instanceof HasOne) {
                    $data[$relation->name][$parts[1]] = $value;
                    continue;
                }

                // HasMany
                if ($relation instanceof HasMany) {
                    $hasManyId = $row[$relation->idField()];

                    if ($hasManyId === null) {
                        // Empty has many relations are initialized it with an empty array
                        $data[$relation->name] ??= [];
                        continue;
                    }

                    $hasManyRelations[$relation->name] ??= $relation;

                    $data[$relation->name][$hasManyId][str_replace($mainField . '.', '', $field)] = $value;
                }
            }
        }

        foreach ($hasManyRelations as $name => $hasMany) {
            $data[$name] = array_values($data[$name]);
        }

        return $data;
    }
}
