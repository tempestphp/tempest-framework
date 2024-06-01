<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentBag;

final class Invocation
{
    public function __construct(
        public ConsoleArgumentBag $argumentBag,
        public ?ConsoleCommand $consoleCommand = null
    ) {
    }
}
