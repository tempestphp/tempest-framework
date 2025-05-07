<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Core\Application;
use Tempest\Core\Kernel;
use Tempest\Core\Tempest;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private Container $container,
        private ConsoleArgumentBag $argumentBag,
    ) {}

    /** @param \Tempest\Discovery\DiscoveryLocation[] $discoveryLocations */
    public static function boot(
        string $name = 'Tempest',
        ?string $root = null,
        array $discoveryLocations = [],
    ): self {
        $container = Tempest::boot($root, $discoveryLocations);

        $application = $container->get(ConsoleApplication::class);

        // Application-specific config
        $consoleConfig = $container->get(ConsoleConfig::class);
        $consoleConfig->name = $name;

        return $application;
    }

    public function run(): void
    {
        $exitCode = $this->container->get(ExecuteConsoleCommand::class)($this->argumentBag->getCommandName());

        $exitCode = is_int($exitCode) ? $exitCode : $exitCode->value;

        if ($exitCode < 0 || $exitCode > 255) {
            throw new InvalidExitCode($exitCode);
        }

        $this->container->get(Kernel::class)->shutdown($exitCode);
    }
}
