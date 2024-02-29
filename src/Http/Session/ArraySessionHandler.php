<?php

namespace Tempest\Http\Session;

use SessionHandlerInterface;

// Session Handler - Determines how the session is stored.
// Session Driver - Determines how the session is serialized.
// Session

final class ArraySessionHandler implements SessionHandlerInterface
{
    /**
     * @var array<array{
     *     data: string,
     *     time: int
     * }>
     */
    private array $storage = [];

    public function __construct(
        private readonly int $validForMinutes = 60
    ) {}

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        unset($this->storage[$id]);

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $expiration = time() - $max_lifetime;
        $sessionsDeleted = 0;

        foreach ($this->storage as $id => $session) {
            if ($session['time'] < $expiration) {
                unset($this->storage[$id]);
                $sessionsDeleted++;
            }
        }

        return $sessionsDeleted;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        if (! isset($this->storage[$id])) {
            return false;
        }

        $session = $this->storage[$id];
        $expiration = $session['time'] + ($this->validForMinutes * 60);

        if (time() >= $expiration) {
            return $session['data'];
        }

        unset($this->storage[$id]);

        return false;
    }

    public function write(string $id, string $data): bool
    {
        $this->storage[$id] = [
            'data' => $data,
            'time' => time(),
        ];

        return true;
    }
}