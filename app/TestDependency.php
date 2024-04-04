<?php

namespace App;

final readonly class TestDependency
{
    public function __construct(public string $input) {}
}