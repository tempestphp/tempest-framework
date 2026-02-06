<?php

final class FullyQualifiedMapCall
{
    public function __invoke(array $data)
    {
        return \Tempest\map($data)->to(Author::class);
    }
}
