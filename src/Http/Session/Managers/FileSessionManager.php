<?php

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionManager;
use Tempest\Http\Session\SessionId;
use Throwable;
use function Tempest\path;

final readonly class FileSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $sessionConfig,
    ) {}

    public function create(SessionId $id): Session
    {
        $this->persist($id);

        return new Session($id, $this->clock->now());
    }

    public function put(SessionId $id, string $key, mixed $value): void
    {
        $this->persist($id, $this->getData($id) + [$key => $value]);
    }

    public function get(SessionId $id, string $key, mixed $default = null): mixed
    {
        return $this->getData($id)[$key] ?? $default;
    }

    public function remove(SessionId $id, string $key): void
    {
        $data = $this->getData($id);

        unset($data[$key]);

        $this->persist($id, $data);
    }

    public function destroy(SessionId $id): void
    {
        unlink($this->getPath($id));
    }

    public function isValid(SessionId $id): bool
    {
        // TODO: add check for timeout
        return $this->resolve($id) !== null;
    }

    private function getPath(SessionId $id): string
    {
        return path($this->sessionConfig->path, $id);
    }

    private function resolve(SessionId $id): ?Session
    {
        $path = $this->getPath($id);

        $content = @file_get_contents($path);

        try {
            return unserialize($content);
        } catch (Throwable) {
            return null;
        }
    }

    private function getData(SessionId $id): array
    {
        return $this->resolve($id)->data ?? [];
    }

    private function persist(SessionId $id, array $data = []): void
    {
        $session = $this->resolve($id) ?? $this->create($id);
        $path = $this->getPath($id);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        $session->data = $data;

        file_put_contents($path, serialize($session));
    }

    public function cleanup(): void
    {
        // TODO: Implement cleanup() method.
    }
}