<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Core\Application;
use Tempest\Core\Kernel;
use Tempest\Core\Tempest;
use Tempest\Support\Str;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private Container $container,
        private ConsoleArgumentBag $argumentBag,
    ) {}

    /**
     * Boots the console application.
     *
     * @param string|null $root The root directory of the application. By default, the current working directory.
     * @param \Tempest\Discovery\DiscoveryLocation[] $discoveryLocations The locations to use for class discovery.
     * @param string|null $internalStorage The *absolute* internal storage directory for Tempest.
     * @param string $name The name of the console application.
     * @param bool $loadBuiltInCommands Whether to load built-in Tempest console commands.
     */
    public static function boot(
        ?string $root = null,
        array $discoveryLocations = [],
        ?string $internalStorage = null,
        ?string $name = null,
        ?bool $loadBuiltInCommands = true,
    ): self {
        if (! $internalStorage && $name) {
            $internalStorage = sprintf('.%s', Str\to_kebab_case($name));
        }

        $container = Tempest::boot($root, $discoveryLocations, $internalStorage);

        $consoleConfig = $container->get(ConsoleConfig::class);
        $consoleConfig->name ??= $name;
        $consoleConfig->loadBuiltInCommands = $loadBuiltInCommands ?? $consoleConfig->loadBuiltInCommands;

        return $container->get(ConsoleApplication::class);
    }

    public function run(): never
    {
        $exitCode = $this->container->get(ExecuteConsoleCommand::class)($this->argumentBag->getCommandName());

        $exitCode = is_int($exitCode) ? $exitCode : $exitCode->value;

        if ($exitCode < 0 || $exitCode > 255) {
            throw new ExitCodeWasInvalid($exitCode);
        }

        $this->container->get(Kernel::class)->shutdown($exitCode);
    }
}
