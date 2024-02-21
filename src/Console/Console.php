<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\InitializedBy;

#[InitializedBy(ConsoleInitializer::class)]
interface Console extends ConsoleInput, ConsoleOutput
{
}
