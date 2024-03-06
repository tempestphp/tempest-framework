<?php

namespace Tempest\Http\Session;

use Tempest\AppConfig;
use Tempest\Clock\Clock;
use Throwable;
use function Tempest\path;

final readonly class FileSession implements Session
{
    public function __construct(
        private Clock $clock,
        private AppConfig $appConfig,
        private SessionId $id,
    ) {}

    public function create(): void
    {
        $this->persist();
    }

    public function put(string $key, mixed $value): void
    {
        $this->persist($this->getData() + [$key => $value]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getData()[$key] ?? $default;
    }

    public function remove(string $key): void
    {
        $data = $this->getData();

        unset($data[$key]);

        $this->persist($data);
    }

    public function destroy(): void
    {
        unlink($this->getPath());
    }

    public function isValid(): bool
    {
        // TODO: add check for timeout
        return $this->resolve() !== null;
    }

    private function getPath(): string
    {
        // TODO: make path configurable via AppConfig
        return path($this->appConfig->root, "sessions/{$this->id}");
    }

    private function resolve(): ?array
    {
        $path = $this->getPath();

        $content = @file_get_contents($path);

        try {
            return unserialize($content);
        } catch (Throwable) {
            return null;
        }
    }

    private function getData(): array
    {
        return $this->resolve()['data'] ?? [];
    }

    private function persist(array $data = []): void
    {
        // TODO: refactor to a data object instead of array
        $session = $this->resolve() ?? [
            'data' => [],
            'time' => $this->clock->time(),
        ];

        $path = $this->getPath();
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        $session['data'] = $data;

        file_put_contents($path, serialize($session));
    }
}