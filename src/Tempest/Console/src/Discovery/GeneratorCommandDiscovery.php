<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use Tempest\Console\ConsoleCommand;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ClassReflector;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Core\Discovery;
use Tempest\Container\Container;
use Tempest\Console\GeneratorCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Generation\StubFileGenerator;

final readonly class GeneratorCommandDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        protected ConsoleConfig $consoleConfig,
    ) {}

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $generatorCommand = $method->getAttribute(GeneratorCommand::class);
            $returnType       = $method->getReturnType();

            if ( is_null( $generatorCommand )) {
                continue;
            }

            if ( $returnType?->getName() !== StubFileGenerator::class ) {
                continue;
            }

            $this->consoleConfig->addCommand($method, $generatorCommand->getConsoleCommand());
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->consoleConfig->commands);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $commands = unserialize($payload, ['allowed_classes' => [GeneratorCommand::class, MethodReflector::class]]);

        $this->consoleConfig->commands = $commands;
    }
}
