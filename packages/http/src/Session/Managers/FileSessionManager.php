<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;

use function Tempest\event;
use function Tempest\internal_storage_path;

final readonly class FileSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $sessionConfig,
    ) {}

    public function getOrCreate(SessionId $id): Session
    {
        $now = $this->clock->now();
        $session = $this->load($id);

        if ($session === null) {
            $session = new Session(
                id: $id,
                createdAt: $now,
                lastActiveAt: $now,
            );

            event(new SessionCreated($session));
        }

        return $session;
    }

    public function save(Session $session): void
    {
        $session->lastActiveAt = $this->clock->now();

        Filesystem\write_file(
            filename: $this->getPath($session->id),
            content: serialize($session),
            flags: LOCK_EX,
        );
    }

    public function delete(Session $session): void
    {
        $path = $this->getPath($session->id);

        Filesystem\delete($path);

        event(new SessionDeleted($session->id));
    }

    public function isValid(Session $session): bool
    {
        return $this->clock->now()->before(
            other: $session->lastActiveAt->plus($this->sessionConfig->expiration),
        );
    }

    public function deleteExpiredSessions(): void
    {
        $sessionFiles = glob(internal_storage_path($this->sessionConfig->path, '/*'));

        if ($sessionFiles === false) {
            return;
        }

        foreach ($sessionFiles as $sessionFile) {
            $id = new SessionId(pathinfo($sessionFile, flags: PATHINFO_FILENAME));
            $session = $this->load($id);

            if ($session === null) {
                continue;
            }

            if ($this->isValid($session)) {
                continue;
            }

            $this->delete($session);
        }
    }

    private function load(SessionId $id): ?Session
    {
        $path = $this->getPath($id);

        try {
            if (! Filesystem\is_file($path)) {
                return null;
            }

            return unserialize(
                data: Filesystem\read_locked_file($path),
                options: [
                    'allowed_classes' => true,
                ],
            );
        } catch (Filesystem\Exceptions\FilesystemException) {
            return null;
        }
    }

    private function getPath(SessionId $id): string
    {
        return internal_storage_path($this->sessionConfig->path, (string) $id);
    }
}
