<?php

declare(strict_types=1);

namespace Tempest\CommandBus\AsyncCommandRepositories;

use Tempest\CommandBus\CommandRepository;
use Tempest\CommandBus\Exceptions\PendingCommandCouldNotBeResolved;
use Tempest\Support\Filesystem;

use function Tempest\Support\arr;

final readonly class FileCommandRepository implements CommandRepository
{
    public function store(string $uuid, object $command): void
    {
        $payload = serialize($command);

        Filesystem\write_file(__DIR__ . "/../stored-commands/{$uuid}.pending.txt", $payload);
    }

    public function findPendingCommand(string $uuid): object
    {
        $path = __DIR__ . "/../stored-commands/{$uuid}.pending.txt";

        if (! Filesystem\is_file($path)) {
            throw new PendingCommandCouldNotBeResolved($uuid);
        }

        $payload = Filesystem\read_file($path);

        return unserialize($payload);
    }

    public function markAsDone(string $uuid): void
    {
        Filesystem\delete_file(__DIR__ . "/../stored-commands/{$uuid}.pending.txt");
    }

    public function markAsFailed(string $uuid): void
    {
        if (! Filesystem\is_file(__DIR__ . "/../stored-commands/{$uuid}.pending.txt")) {
            return;
        }

        rename(
            from: __DIR__ . "/../stored-commands/{$uuid}.pending.txt",
            to: __DIR__ . "/../stored-commands/{$uuid}.failed.txt",
        );
    }

    public function getPendingCommands(): array
    {
        return arr(glob(__DIR__ . '/../stored-commands/*.pending.txt'))
            ->mapWithKeys(function (string $path) {
                if (! Filesystem\is_file($path)) {
                    return;
                }

                $uuid = str_replace('.pending.txt', '', pathinfo($path, PATHINFO_BASENAME));
                $payload = Filesystem\read_file($path);

                yield $uuid => unserialize($payload);
            })
            ->toArray();
    }
}
