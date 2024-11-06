<?php

declare(strict_types=1);

namespace Tempest\CommandBus\AsyncCommandRepositories;

use Tempest\CommandBus\AsyncCommandRepository;
use function Tempest\Support\arr;

final readonly class FileRepository implements AsyncCommandRepository
{
    public function store(string $uuid, object $command): void
    {
        $payload = serialize($command);

        file_put_contents(__DIR__ . "/../stored-commands/{$uuid}.txt", $payload);
    }

    public function find(string $uuid): object
    {
        $path = __DIR__ . "/../stored-commands/{$uuid}.txt";

        $payload = file_get_contents($path);

        return unserialize($payload);
    }

    public function remove(string $uuid): void
    {
        $path = __DIR__ . "/../stored-commands/{$uuid}.txt";

        unlink($path);
    }

    public function available(): array
    {
        return arr(glob(__DIR__ . "/../stored-commands/*.txt"))
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->toArray();
    }
}
