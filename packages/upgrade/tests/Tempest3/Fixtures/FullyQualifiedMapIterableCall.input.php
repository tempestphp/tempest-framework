<?php

use Tempest\Support\Arr;

final class FullyQualifiedMapIterableCall
{
    public function __invoke(array $data)
    {
        return Arr\map_iterable($data, fn ($item) => $item * 2);
    }
}
