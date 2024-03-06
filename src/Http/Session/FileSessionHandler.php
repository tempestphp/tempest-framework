<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use SessionHandlerInterface;
use Tempest\AppConfig;
use Tempest\Clock\Clock;
use function Tempest\path;

final class FileSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        private readonly Clock $clock,
        private readonly AppConfig $appConfig,
        private readonly int $validForMinutes = 60,
    ) {
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        unlink($this->getPath($id));

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
        //
        //        $expiration = $this->clock->time() - $max_lifetime;
        //        $sessionsDeleted = 0;
        //
        //        $files = glob($this->getPath('*'));
        //
        //        foreach ($files as $session) {
        //            if ($session['time'] < $expiration) {
        //                unset($this->storage[$id]);
        //                $sessionsDeleted++;
        //            }
        //        }
        //
        //        return $sessionsDeleted;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $session = $this->getSession($id);

        if (! $session) {
            return false;
        }

        $expiration = $session['time'] + ($this->validForMinutes * 60);

        if ($this->clock->time() <= $expiration) {
            return $session['data'];
        }

        $this->destroy($id);

        return '';
    }

    public function write(string $id, string $data): bool
    {
        $session = [
            'data' => $data,
            'time' => $this->clock->time(),
        ];

        $path = $this->getPath($id);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents($path, serialize($session));

        return true;
    }

    private function getPath(string $id): string
    {
        return path($this->appConfig->root, "sessions/{$id}");
    }

    private function getSession(string $id): ?array
    {
        $filename = $this->getPath($id);

        if (! is_file($filename)) {
            $this->write($id, '');
        }

        return unserialize(file_get_contents($filename));
    }
}
