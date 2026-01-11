<?php

use function Tempest\Support\Arr\map_iterable;

final class MapIterableNamespaceChange
{
    public function __invoke(array $data)
    {
        return map_iterable($data, fn ($item) => $item * 2);
    }
}
