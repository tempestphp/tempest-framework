<?php

declare(strict_types=1);

namespace Tempest\Mappers;

use ReflectionClass;
use Tempest\Database\Query;
use Tempest\Interfaces\Mapper;

use function Tempest\make;

final readonly class QueryToModelMapper implements Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return $data instanceof Query;
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        /** @var \Tempest\Database\Query $data */
        if ($data->bindings['id'] ?? null) {
            return make($objectOrClass)->from($this->resolveData($objectOrClass, $data->fetchFirst()));
        } else {
            return array_map(
                fn (array $item) => make($objectOrClass)->from($this->resolveData($objectOrClass, $item)),
                $data->fetch(),
            );
        }
    }

    private function resolveData(object|string $objectOrClass, array $data): array
    {
        $className = (new ReflectionClass($objectOrClass))->getShortName();

        $values = [];

        foreach ($data as $key => $value) {
            if (! strpos($key, ':')) {
                $values[$key] = $value;

                continue;
            }

            // TODO: we need to properly map table names to relation fields, as they aren't always the same
            [$table, $field] = explode(':', $key);

            if ($table === $className) {
                $values[$field] = $value;
            } else {
                $values[lcfirst($table)][$field] = $value;
            }
        }

        return $values;
    }
}
