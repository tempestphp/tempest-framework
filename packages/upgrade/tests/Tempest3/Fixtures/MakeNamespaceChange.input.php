<?php

use function Tempest\make;

final class MakeNamespaceChange
{
    public function __invoke()
    {
        return make(Author::class)->from(['name' => 'Jon Doe']);
    }
}
