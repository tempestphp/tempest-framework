<?php

namespace App\Console;

use Tempest\Console\ConsoleCommand;

final readonly class Package
{
    #[ConsoleCommand]
    public function list(): void {}

    #[ConsoleCommand]
    public function info(string $name): void {}
}