<?php

declare(strict_types=1);

namespace Tempest\Interface;

use Tempest\Console\ConsoleInitializer;
use Tempest\Container\InitializedBy;

#[InitializedBy(ConsoleInitializer::class)]
interface Console extends ConsoleInput, ConsoleOutput
{
}
