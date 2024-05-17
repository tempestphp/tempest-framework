<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Log\Channels\LogChannel;
use Tempest\Log\LogConfig;

final readonly class LogHandlerDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/log-handler-discovery.cache.php';

    public function __construct(
        private LogConfig $logConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        if (
            ! $class->isInstantiable()
            || ! $class->implementsInterface(LogChannel::class)
        ) {
            return;
        }

        $this->logConfig->channels[$class->getName()] = $class->getName();
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->logConfig->channels));
    }

    public function restoreCache(Container $container): void
    {
        $channels = unserialize(file_get_contents(self::CACHE_PATH));

        $this->logConfig->channels = $channels;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
