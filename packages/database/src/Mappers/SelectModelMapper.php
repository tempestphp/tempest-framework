<?php

namespace Tempest\Database\Mappers;

use Tempest\Database\BelongsTo;
use Tempest\Database\BelongsToMany;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Eager;
use Tempest\Database\HasMany;
use Tempest\Database\HasManyThrough;
use Tempest\Database\HasOne;
use Tempest\Database\HasOneThrough;
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
                if ($relation->property->hasAttribute(Eager::class)) {
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

            if ($relation instanceof BelongsTo || $relation instanceof HasOne || $relation instanceof HasOneThrough) {
                if (is_array($data[$relation->name] ?? null)) {
                    $relationModel = inspect($relation);
                    $data[$relation->name] = $this->values($relationModel, $data[$relation->name]);
                }

                if ($relation->property->isNullable() && $this->relationDataIsEmpty($data[$relation->name] ?? null)) {
                    $data[$relation->name] = null;
                }

                continue;
            }

            if ($relation instanceof HasMany || $relation instanceof HasManyThrough || $relation instanceof BelongsToMany) {
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

    private function relationDataIsEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (! $this->relationDataIsEmpty($item)) {
                return false;
            }
        }

        return true;
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

                if ($relation instanceof BelongsTo || $relation instanceof HasOne || $relation instanceof HasOneThrough) {
                    $key .= $relation->name . '.';
                    $originalKey .= $relation->name . '.';
                } elseif ($relation instanceof HasMany || $relation instanceof HasManyThrough || $relation instanceof BelongsToMany) {
                    $hasManyId = $data->get($key . $relation->idField()) ?? $row[$originalKey . $relation->idField()] ?? null;

                    $originalKey .= $relation->name . '.';

                    if ($hasManyId === null) {
                        if ($data->get($key . $relation->name) === null) {
                            $data->set($key . $relation->name, []);
                        }

                        break;
                    }

                    $key .= $relation->name . '.' . $hasManyId . '.';
                } else {
                    $key .= $part;
                    break;
                }

                $currentModel = inspect($relation);
            }

            if ($key !== '' && $key !== '0') {
                $data->set($key, $value);
            }
        }

        return $data->toArray();
    }
}
