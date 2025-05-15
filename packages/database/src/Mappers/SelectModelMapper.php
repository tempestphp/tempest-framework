<?php

namespace Tempest\Database\Mappers;

use Tempest\Database\Builder\ModelInspector;
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
                $mainField = explode('.', $field)[0];

                // Main fields
                if ($mainField === $mainTable) {
                    $data[substr($field, strlen($mainTable) + 1)] = $value;
                    continue;
                }

                // BelongsTo
                if ($belongsTo = $model->getBelongsTo($mainField)) {
                    $data[$belongsTo->property->getName()][str_replace($mainField . '.', '', $field)] = $value;
                }

                // HasOne
                if ($hasOne = $model->getHasOne($mainField)) {
                    $data[$hasOne->property->getName()][str_replace($mainField . '.', '', $field)] = $value;
                }

                // HasMany
                if ($hasMany = $model->getHasMany($mainField)) {
                    $hasManyRelations[$mainField] ??= $hasMany;

                    $hasManyId = $row[$hasMany->idField()];

                    $data[$hasMany->property->getName()][$hasManyId][str_replace($mainField . '.', '', $field)] = $value;
                }
            }
        }

        foreach ($hasManyRelations as $name => $hasMany) {
            $data[$name] = array_values($data[$name]);
        }

        return $data;
    }
}
