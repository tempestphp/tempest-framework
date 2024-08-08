<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use DateTimeImmutable;
use RuntimeException;
use Tempest\Clock\Clock;
use function Tempest\event;
use Tempest\Http\Session\FlashValue;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use function Tempest\path;
use Tempest\Validation\Rules\AlphaNumeric;
use Tempest\Validation\Rules\Between;
use Tempest\Validation\Rules\NotEmpty;
use Throwable;

final readonly class FileSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $sessionConfig,
    ) {
    }

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

        $validUntil = $session->createdAt->getTimestamp() + $this->sessionConfig->expirationInSeconds;

        return $validUntil - $this->clock->time() > 0;
    }

    private function getPath(SessionId $id): string
    {
        return path($this->sessionConfig->path, (string) $id);
    }

    private function resolve(SessionId $id): ?Session
    {
        $path = $this->getPath($id);

        try {
            $content = @file_get_contents($path);

            return unserialize($content, ['allowed_classes' => [
                SessionId::class,
                Session::class,
                DateTimeImmutable::class,
                FlashValue::class,
                NotEmpty::class,
                Between::class,
                AlphaNumeric::class,
            ]]);
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
        $session = $this->resolve($id) ?? new Session(
            id: $id,
            createdAt: $this->clock->now(),
        );

        $path = $this->getPath($id);
        $dir = dirname($path);

        if (! is_dir($dir) && ! mkdir($dir, recursive: true) && ! is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        if ($data !== null) {
            $session->data = $data;
        }

        file_put_contents($path, serialize($session));

        return $session;
    }

    public function cleanup(): void
    {
        $sessionFiles = glob(path($this->sessionConfig->path, '/*'));

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
