<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Installer;

use LogicException;
use Tempest\Console\Console;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Http\Session\CleanupStrategy;

use function Tempest\src_path;

final class SessionInstaller
{
    use PublishesFiles;

    public function __construct(
        private readonly MigrationManager $migrationManager,
        private readonly Console $console,
    ) {}

    #[Installer('Sessions', alias: 'sessions')]
    public function install(
        ?SessionStorage $storage = null,
        ?bool $migrate = null,
    ): void {
        /** @var null|SessionStorage $storage */
        $storage ??= $this->console->ask(
            question: 'Which session storage do you want to use?',
            options: SessionStorage::class,
            default: SessionStorage::FILE,
        );

        if (! $storage instanceof SessionStorage) {
            $this->console->error('Invalid session storage selected.');
            return;
        }

        $cleanupStrategy = match ($storage) {
            SessionStorage::REDIS => null,
            default => $this->resolveCleanupStrategy(),
        };

        $migration = match ($storage) {
            SessionStorage::DATABASE => $this->publish(
                source: __DIR__ . '/CreateSessionsTable.php',
                destination: src_path('Sessions/CreateSessionsTable.php'),
            ),
            default => null,
        };

        $this->publish(
            source: $this->resolveSessionConfigStub($storage, $cleanupStrategy),
            destination: $this->promptTargetPath(src_path('Sessions/session.config.php')),
            confirm: false,
        );

        if ($cleanupStrategy && $this->shouldPublishCleanupCommand($cleanupStrategy)) {
            $this->publish(
                source: __DIR__ . '/../CleanupSessionsCommand.php',
                destination: $this->promptTargetPath(src_path('Sessions/CleanupSessionsCommand.php')),
                confirm: false,
            );
        }

        $this->publishImports();

        if ($migration && $this->shouldMigrate($migrate)) {
            $this->migrationManager->up();
        }
    }

    private function resolveCleanupStrategy(): CleanupStrategy
    {
        $strategy = [
            'Random requests' => CleanupStrategy::RANDOM_REQUESTS,
            'Every request' => CleanupStrategy::EVERY_REQUEST,
            'Disabled (requires manual cleanup)' => CleanupStrategy::DISABLED,
        ];

        $result = $this->console->ask(
            question: 'Which session cleanup strategy do you want to use?',
            options: array_keys($strategy),
            default: array_key_first($strategy),
        );

        return $strategy[$result];
    }

    private function resolveSessionConfigStub(SessionStorage $storage, ?CleanupStrategy $cleanupStrategy): string
    {
        if ($storage === SessionStorage::REDIS) {
            return __DIR__ . '/session.redis.config.stub.php';
        }

        return match ([$storage, $cleanupStrategy]) {
            [SessionStorage::FILE, CleanupStrategy::EVERY_REQUEST] => __DIR__ . '/session.file.every-request.config.stub.php',
            [SessionStorage::FILE, CleanupStrategy::RANDOM_REQUESTS] => __DIR__ . '/session.file.random-requests.config.stub.php',
            [SessionStorage::FILE, CleanupStrategy::DISABLED] => __DIR__ . '/session.file.disabled.config.stub.php',
            [SessionStorage::DATABASE, CleanupStrategy::EVERY_REQUEST] => __DIR__ . '/session.database.every-request.config.stub.php',
            [SessionStorage::DATABASE, CleanupStrategy::RANDOM_REQUESTS] => __DIR__ . '/session.database.random-requests.config.stub.php',
            [SessionStorage::DATABASE, CleanupStrategy::DISABLED] => __DIR__ . '/session.database.disabled.config.stub.php',
            default => throw new LogicException('Cleanup strategy must be provided for non-Redis session drivers.'),
        };
    }

    private function shouldMigrate(?bool $migrate): bool
    {
        if (is_bool($migrate)) {
            return $migrate;
        }

        return $this->console->confirm('Do you want to execute migrations?', default: false);
    }

    private function shouldPublishCleanupCommand(CleanupStrategy $cleanupStrategy): bool
    {
        if ($cleanupStrategy !== CleanupStrategy::DISABLED) {
            return false;
        }

        return $this->console->confirm(
            'Session cleanup is disabled. Do you want to publish a session cleanup command?',
            default: true,
        );
    }
}
