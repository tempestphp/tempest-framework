<?php

use function Tempest\map;

final class MapNamespaceChange
{
    public function __invoke(array $data)
    {
        return map($data)->to(Author::class);
    }
}
