<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\Container\InitializedBy;

#[InitializedBy(ApplicationInitializer::class)]
interface Application
{
    public function run(): void;
}
