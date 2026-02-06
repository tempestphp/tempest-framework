<?php

final class FullyQualifiedMakeCall
{
    public function __invoke()
    {
        return \Tempest\make(Author::class)->from(['name' => 'Jon Doe']);
    }
}
