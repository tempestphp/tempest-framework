<?php

namespace Tempest\Database\Mappers;

use Exception;
use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Mapper\Mapper;

use Tempest\Support\Arr\MutableArray;
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

            if (! $relation instanceof HasMany) {
                continue;
            }

            $mapped = [];
            $relationModel = model($relation);

            foreach ($value as $item) {
                $mapped[] = $this->values($relationModel, $item);
            }

            $data[$key] = $mapped;
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
                    $id = $data->get($key . $relation->idField())
                        ?? $row[$originalKey . $relation->idField()]
                        ?? null;

                    $key .= $relation->name . '.' . $id . '.';
                    $originalKey .= $relation->name . '.';
                } else {
                    $key .= $part;
                    break;
                }

                $currentModel = model($relation);
            }

            $data->set($key, $value);
        }

        return $data->toArray();
    }
}
