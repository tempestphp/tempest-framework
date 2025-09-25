<?php

namespace Tempest\Database\Mappers;

use Exception;
use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Mapper\Mapper;
use Tempest\Support\Arr\MutableArray;

use function Tempest\Database\inspect;
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
        $model = inspect($to);

        $idField = $model->getQualifiedPrimaryKey();

        $parsed = arr($from)
            ->groupBy(fn (array $data, int|string $i) => $idField !== null ? $data[$idField] ?? $i : $i)
            ->map(fn (array $rows) => $this->normalizeFields($model, $rows))
            ->values();

        return map($parsed->toArray())->collection()->to($to);
    }

    private function normalizeFields(ModelInspector $model, array $rows): array
    {
        $data = new MutableArray();

        foreach ($rows as $row) {
            $this->normalizeRow($model, $row, $data);
        }

        return $this->values($model, $data->toArray());
    }

    private function values(ModelInspector $model, array $data): array
    {
        foreach ($data as $key => $value) {
            $relation = $model->getRelation($key);

            if ($relation instanceof BelongsTo) {
                $relationModel = inspect($relation);

                if (($data[$relation->name][$relationModel->getPrimaryKeyProperty()->getName()] ?? null) === null) {
                    $data[$relation->name] = null;
                }

                continue;
            }

            if ($relation instanceof HasMany) {
                $mapped = [];
                $relationModel = inspect($relation);

                foreach ($value as $item) {
                    $mapped[] = $this->values($relationModel, $item);
                }

                $data[$key] = $mapped;
            }
        }

        return $data;
    }

    public function normalizeRow(ModelInspector $model, array $row, MutableArray $data): array
    {
        $mainTable = $model->getTableName();

        foreach ($row as $field => $value) {
            $parts = explode('.', $field);

            $mainField = $parts[0];

            // Main fields
            if ($mainField === $mainTable) {
                $data->set($parts[1], $value);
                continue;
            }

            // Relations
            $key = '';
            $originalKey = '';
            $currentModel = $model;

            foreach ($parts as $part) {
                $relation = $currentModel->getRelation($part);

                if ($relation instanceof BelongsTo || $relation instanceof HasOne) {
                    $key .= $relation->name . '.';
                    $originalKey .= $relation->name . '.';
                } elseif ($relation instanceof HasMany) {
                    $hasManyId = $data->get($key . $relation->idField()) ?? $row[$originalKey . $relation->idField()] ?? null;

                    $originalKey .= $relation->name . '.';

                    if (! $data->hasKey(trim($originalKey, '.'))) {
                        $data->set(trim($originalKey, '.'), []);
                    }

                    if ($hasManyId === null) {
                        break;
                    }

                    $key .= $relation->name . '.' . $hasManyId . '.';
                } else {
                    $key .= $part;
                    break;
                }

                $currentModel = inspect($relation);
            }

            if ($key) {
                $data->set($key, $value);
            }
        }

        return $data->toArray();
    }
}
