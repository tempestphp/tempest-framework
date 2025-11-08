<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Throwable;

use function Tempest\event;
use function Tempest\internal_storage_path;

final readonly class FileSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $sessionConfig,
    ) {}

    public function create(SessionId $id): Session
    {
        return $this->persist($id);
    }

    public function set(SessionId $id, string $key, mixed $value): void
    {
        $this->persist($id, [...$this->getData($id), ...[$key => $value]]);
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

        event(new SessionDestroyed($id));
    }

    public function isValid(SessionId $id): bool
    {
        $session = $this->resolve($id);

        if ($session === null) {
            return false;
        }

        if (! ($session->lastActiveAt ?? null)) {
            return false;
        }

        return $this->clock->now()->before(
            other: $session->lastActiveAt->plus($this->sessionConfig->expiration),
        );
    }

    private function getPath(SessionId $id): string
    {
        return internal_storage_path($this->sessionConfig->path, (string) $id);
    }

    private function resolve(SessionId $id): ?Session
    {
        $path = $this->getPath($id);

        try {
            if (! Filesystem\is_file($path)) {
                return null;
            }

            $file_pointer = fopen($path, 'rb');
            flock($file_pointer, LOCK_SH);

            $content = Filesystem\read_file($path);

            flock($file_pointer, LOCK_UN);
            fclose($file_pointer);

            return unserialize($content, ['allowed_classes' => true]);
        } catch (Throwable) {
            return null;
        }
    }

    public function all(SessionId $id): array
    {
        return $this->getData($id);
    }

    /**
     * @return array<mixed>
     */
    private function getData(SessionId $id): array
    {
        return $this->resolve($id)->data ?? [];
    }

    /**
     * @param array<mixed>|null $data
     */
    private function persist(SessionId $id, ?array $data = null): Session
    {
        $now = $this->clock->now();
        $session = $this->resolve($id) ?? new Session(
            id: $id,
            createdAt: $now,
            lastActiveAt: $now,
        );

        $session->lastActiveAt = $now;

        if ($data !== null) {
            $session->data = $data;
        }

        Filesystem\write_file($this->getPath($id), serialize($session), LOCK_EX);

        return $session;
    }

    public function cleanup(): void
    {
        $sessionFiles = glob(internal_storage_path($this->sessionConfig->path, '/*'));

        foreach ($sessionFiles as $sessionFile) {
            $id = new SessionId(pathinfo($sessionFile, PATHINFO_FILENAME));

            $session = $this->resolve($id);

            if ($session === null) {
                continue;
            }

            if ($this->isValid($session->id)) {
                continue;
            }

            $session->destroy();
        }
    }
}
