<?php

declare(strict_types=1);

namespace Tempest\ORM\Mappers;

use Tempest\Database\Query;
use Tempest\Interfaces\Mapper;

final readonly class SqlMapper implements Mapper
{
    public function canMap(mixed $data): bool
    {
        return $data instanceof Query;
    }

    public function map(string $className, mixed $data): array|object
    {
        /** @var \Tempest\Database\Query $data */
        if ($data->bindings['id'] ?? null) {
            return make($className)->from($data->fetchFirst());
        } else {
            return array_map(
                fn (array $item) => make($className)->from($item),
                $data->fetch(),
            );
        }
    }
}
