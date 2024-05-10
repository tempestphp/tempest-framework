<?php

declare(strict_types=1);

namespace Tempest\Console;

final class Invocation
{
    public function __construct(
        public ConsoleArgumentBag $argumentBag,
        public ?ConsoleCommand $consoleCommand = null
    ) {
    }
}
