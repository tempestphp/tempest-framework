<?php

namespace Tempest\Database\Mappers;

use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Mapper\Context;
use Tempest\Mapper\Mapper;
use Tempest\Support\Arr\MutableArray;

use function Tempest\Database\inspect;
use function Tempest\Mapper\map;
use function Tempest\Support\arr;

#[SkipDiscovery]
final class SelectModelMapper implements Mapper
{
    public function __construct(
        private Context $context,
    ) {}

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

        $objects = map($parsed->toArray())
            ->in($this->context)
            ->collection()
            ->to($to);

        foreach ($objects as $i => $object) {
            foreach ($model->getRelations() as $relation) {
                // When a nullable BelongsTo relation wasn't loaded, we need to make sure to unset it if it has a default value.
                // If we wouldn't do this, the default value would overwrite the "unloaded" value on the next time saving the model
                if (! $relation instanceof BelongsTo) {
                    continue;
                }

                if (! $relation->property->isNullable()) {
                    continue;
                }

                if (! $relation->property->hasDefaultValue()) {
                    continue;
                }

                if (! array_key_exists($relation->name, $parsed[$i] ?? [])) {
                    $relation->property->unset($object);
                }
            }
        }

        return $objects;
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
                if ($relation->property->isNullable() && array_filter($data[$relation->name] ?? []) === []) {
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
