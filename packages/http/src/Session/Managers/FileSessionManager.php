<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionCache;
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
        private SessionCache $cache,
    ) {}

    public function create(SessionId $id): Session
    {
        $session = $this->resolve(id:$id);

        if ($session) {
            return $session;
        }

        $session = new Session(
            id: $id,
            createdAt: $this->clock->now(),
            lastActiveAt: $this->clock->now(),
            data: [],
        );

        $this->cache->store(session:$session);

        return $session;
    }

    public function destroy(SessionId $id): void
    {
        unlink(filename:$this->getPath(id:$id));

        event(event:new SessionDestroyed(id:$id));
    }

    public function resolve(SessionId $id): ?Session
    {
        $session = $this->cache->find(sessionId:$id);

        if ($session) {
            return $session;
        }

        $path = $this->getPath(id:$id);

        try {
            if (! Filesystem\is_file(path:$path)) {
                return null;
            }

            $file_pointer = fopen(filename:$path, mode:'rb');
            flock(stream:$file_pointer, operation:LOCK_SH);

            $content = Filesystem\read_file(filename:$path);

            flock(stream:$file_pointer, operation:LOCK_UN);
            fclose(stream:$file_pointer);

            $session = unserialize(data:$content, options:['allowed_classes' => true]);

            $this->cache->store(session:$session);

            return $session;
        } catch (Throwable) {
            return null;
        }
    }

    public function persist(Session $session): void
    {
        $session->lastActiveAt = $this->clock->now();

        Filesystem\write_file(filename:$this->getPath(id:$session->id), content:serialize(value:$session), flags:LOCK_EX);
    }

    public function cleanup(): void
    {
        $sessionFiles = glob(pattern:internal_storage_path($this->sessionConfig->path, '/*'));

        foreach ($sessionFiles as $sessionFile) {
            $id = new SessionId(id:pathinfo(path:$sessionFile, flags:PATHINFO_FILENAME));

            $session = $this->resolve(id:$id);

            if ($session === null) {
                continue;
            }

            if ($this->cache->isValid(session:$session)) {
                continue;
            }

            $session->destroy();
        }
    }

    private function getPath(SessionId $id): string
    {
        return internal_storage_path($this->sessionConfig->path, (string) $id);
    }
}
