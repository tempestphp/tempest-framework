<?php

declare(strict_types=1);

namespace Tempest\Database\Mappers;

use Tempest\Database\Query;
use function Tempest\make;
use Tempest\Mapper\Mapper;
use Tempest\Support\ArrayHelper;

final readonly class QueryToModelMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $from instanceof Query;
    }

    public function map(mixed $from, mixed $to): array
    {
        /** @var Query $from */
        return array_map(
            fn (array $item) => make($to)->from($this->resolveData($item)),
            $from->fetch(),
        );
    }

    private function resolveData(array $data): array
    {
        $values = [];

        foreach ($data as $key => $value) {
            $keyParts = explode('.', $key);

            array_shift($keyParts);

            ArrayHelper::set($values, implode('.', $keyParts), $value);
        }

        return $values;
    }
}
