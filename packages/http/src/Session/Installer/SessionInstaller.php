<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Installer;

use InvalidArgumentException;
use LogicException;
use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Http\Session\CleanupStrategy;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\Path\to_absolute_path;

final class SessionInstaller implements Installer
{
    use PublishesFiles;

    private const string DATABASE = 'database';

    private const string FILE = 'file';

    private const string REDIS = 'redis';

    private(set) string $name = 'sessions';

    public function __construct(
        private readonly MigrationManager $migrationManager,
        private readonly Console $console,
        private readonly ConsoleArgumentBag $consoleArgumentBag,
    ) {}

    public function install(): void
    {
        $sessionStrategy = $this->resolveSessionDriver();
        $cleanupStrategy = $sessionStrategy === self::REDIS
            ? null
            : $this->resolveCleanupStrategy();

        $configPath = $this->resolveTargetPath(
            argumentName: 'config-path',
            defaultPath: src_path('Sessions/session.config.php'),
        );

        $migration = $sessionStrategy === self::DATABASE
            ? $this->publish(
                source: __DIR__ . '/CreateSessionsTable.php',
                destination: src_path('Sessions/CreateSessionsTable.php'),
            )
            : null;

        $this->publish(
            source: $this->resolveSessionConfigStub($sessionStrategy, $cleanupStrategy),
            destination: $configPath,
            confirm: false,
        );

        if ($cleanupStrategy && $this->shouldPublishCleanupCommand($cleanupStrategy)) {
            $this->publish(
                source: __DIR__ . '/../CleanupSessionsCommand.php',
                destination: $this->resolveTargetPath(
                    argumentName: 'cleanup-command-path',
                    defaultPath: src_path('Sessions/CleanupSessionsCommand.php'),
                ),
                confirm: false,
            );
        }

        $this->publishImports();

        if ($migration && $this->shouldMigrate()) {
            $this->migrationManager->up();
        }
    }

    private function resolveSessionDriver(): string
    {
        $argument = $this->consoleArgumentBag->get('strategy');

        if ($argument instanceof ConsoleInputArgument && is_string($argument->value)) {
            return match (strtolower(trim($argument->value))) {
                self::FILE => self::FILE,
                self::DATABASE => self::DATABASE,
                self::REDIS => self::REDIS,
                default => throw new InvalidArgumentException('Invalid session storage strategy: ' . $argument->value),
            };
        }

        return $this->ask(
            question: 'Which session storage strategy do you want to use?',
            options: [
                self::FILE => 'File',
                self::DATABASE => 'Database',
                self::REDIS => 'Redis',
            ],
            default: self::FILE,
        );
    }

    private function resolveTargetPath(string $argumentName, string $defaultPath): string
    {
        $argument = $this->consoleArgumentBag->get($argumentName);

        if ($argument instanceof ConsoleInputArgument && is_string($argument->value)) {
            return to_absolute_path(root_path(), $argument->value);
        }

        return $this->promptTargetPath($defaultPath);
    }

    private function resolveCleanupStrategy(): CleanupStrategy
    {
        $argument = $this->consoleArgumentBag->get('cleanup-strategy');

        if ($argument instanceof ConsoleInputArgument && is_string($argument->value)) {
            $strategy = $this->parseCleanupStrategy($argument->value);

            if ($strategy instanceof CleanupStrategy) {
                return $strategy;
            }
        }

        /** @var string $selection */
        $selection = $this->ask(
            question: 'Which session cleanup strategy do you want to use?',
            options: [
                CleanupStrategy::RANDOM_REQUESTS->name => 'Random requests',
                CleanupStrategy::EVERY_REQUEST->name => 'Every request',
                CleanupStrategy::DISABLED->name => 'Disabled (schedule the cleanup command)',
            ],
            default: CleanupStrategy::RANDOM_REQUESTS->name,
        );

        return $this->parseCleanupStrategy($selection);
    }

    private function parseCleanupStrategy(string $input): ?CleanupStrategy
    {
        $normalized = strtoupper(str_replace(['-', ' '], '_', $input));

        return match ($normalized) {
            CleanupStrategy::EVERY_REQUEST->name => CleanupStrategy::EVERY_REQUEST,
            CleanupStrategy::RANDOM_REQUESTS->name => CleanupStrategy::RANDOM_REQUESTS,
            CleanupStrategy::DISABLED->name => CleanupStrategy::DISABLED,
            default => null,
        };
    }

    private function resolveSessionConfigStub(string $sessionStrategy, ?CleanupStrategy $cleanupStrategy): string
    {
        if ($sessionStrategy === self::REDIS) {
            return __DIR__ . '/session.redis.config.stub.php';
        }

        return match ([$sessionStrategy, $cleanupStrategy]) {
            [self::FILE, CleanupStrategy::EVERY_REQUEST] => __DIR__ . '/session.file.every-request.config.stub.php',
            [self::FILE, CleanupStrategy::RANDOM_REQUESTS] => __DIR__ . '/session.file.random-requests.config.stub.php',
            [self::FILE, CleanupStrategy::DISABLED] => __DIR__ . '/session.file.disabled.config.stub.php',
            [self::DATABASE, CleanupStrategy::EVERY_REQUEST] => __DIR__ . '/session.database.every-request.config.stub.php',
            [self::DATABASE, CleanupStrategy::RANDOM_REQUESTS] => __DIR__ . '/session.database.random-requests.config.stub.php',
            [self::DATABASE, CleanupStrategy::DISABLED] => __DIR__ . '/session.database.disabled.config.stub.php',
            default => throw new LogicException('Cleanup strategy must be provided for non-Redis session drivers.'),
        };
    }

    private function shouldMigrate(): bool
    {
        $argument = $this->consoleArgumentBag->get('migrate');

        if (! $argument instanceof ConsoleInputArgument || ! is_bool($argument->value)) {
            return $this->console->confirm('Do you want to execute migrations?', default: false);
        }

        return (bool) $argument->value;
    }

    private function shouldPublishCleanupCommand(CleanupStrategy $cleanupStrategy): bool
    {
        $argument = $this->consoleArgumentBag->get('cleanup-command');

        if ($argument instanceof ConsoleInputArgument && is_bool($argument->value)) {
            return (bool) $argument->value;
        }

        if ($cleanupStrategy !== CleanupStrategy::DISABLED) {
            return false;
        }

        return $this->console->confirm(
            'Session cleanup is disabled. Do you want to publish a session cleanup command?',
            default: true,
        );
    }
}
