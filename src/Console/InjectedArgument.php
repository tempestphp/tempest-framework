<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionProperty;

readonly class InjectedArgument extends Argument
{
    /**
     * @throws ExitException
     */
    public function handle(ConsoleCommand $command): void
    {

    }

    public function shouldInject(): bool
    {
        return ! ! $this->value;
    }

    public static function bool()
    {
        $func = function (bool $x) {

        };

        return new \ReflectionParameter($func, 'x');
    }
}
