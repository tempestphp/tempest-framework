<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\DateTime\DateTime;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Throwable;

use function Tempest\EventBus\event;
use function Tempest\internal_storage_path;

final readonly class FileSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private FileSessionConfig $sessionConfig, // TODO: rename to $config, see RedisSessionManager and DatabaseSessionManager
        private SessionIdResolver $sessionIdResolver,
    ) {}

    public function getOrCreate(SessionId $id): Session
    {
        $now = $this->clock->now();
        $session = $this->load($id);

        if (! $session instanceof Session) {
            $session = new Session(
                id: $id,
                createdAt: $now,
                lastActiveAt: $now,
                isNew: true,
            );

            event(new SessionCreated($session));
        }

        return $session;
    }

    public function save(Session $session): void
    {
        $now = $this->clock->now();
        $path = $this->getPath($session->id);

        $session->lastActiveAt = $now;

        // Unchanged sessions aren't rewritten, their activity is tracked through the file's modification time instead.
        if ($session->isDirty() || ! Filesystem\is_file($path)) {
            Filesystem\write_file(
                filename: $path,
                content: serialize($session),
                flags: LOCK_EX,
            );
        }

        touch($path, $now->getTimestamp()->getSeconds());

        $session->markAsPersisted();
    }

    public function delete(Session $session): void
    {
        $path = $this->getPath($session->id);

        Filesystem\delete($path);

        event(new SessionDeleted($session->id));
    }

    public function regenerate(Session $session): void
    {
        $this->delete($session);

        $session->replaceId($this->sessionIdResolver->issueNewId());

        $this->save($session);
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

        $expiredBefore = $this->clock->now()->minus($this->sessionConfig->expiration)->getTimestamp()->getSeconds();

        foreach ($sessionFiles as $sessionFile) {
            $lastActiveAt = @filemtime($sessionFile);
            if ($lastActiveAt === false) {
                continue;
            }

            if ($lastActiveAt > $expiredBefore) {
                continue;
            }

            $id = new SessionId(pathinfo($sessionFile, flags: PATHINFO_FILENAME));

            Filesystem\delete($sessionFile);

            event(new SessionDeleted($id));
        }
    }

    private function load(SessionId $id): ?Session
    {
        $path = $this->getPath($id);

        try {
            if (! Filesystem\is_file($path)) {
                return null;
            }

            $session = unserialize(
                data: Filesystem\read_locked_file($path),
                options: [
                    'allowed_classes' => true,
                ],
            );

            if (! $session instanceof Session) {
                return null;
            }

            $lastModifiedAt = filemtime($path);

            if ($lastModifiedAt !== false && $lastModifiedAt > $session->lastActiveAt->getTimestamp()->getSeconds()) {
                $session->lastActiveAt = DateTime::fromTimestamp($lastModifiedAt);
            }

            return $session;
        } catch (Throwable) {
            return null;
        }
    }

    private function getPath(SessionId $id): string
    {
        return internal_storage_path($this->sessionConfig->path, (string) $id);
    }
}
