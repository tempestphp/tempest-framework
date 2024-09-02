<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Support\Reflection\ClassReflector;

final readonly class DiscoveryDiscovery implements Discovery
{
    public const string CACHE_PATH = __DIR__ . '/../../../.cache/tempest/discovery-discovery.cache.php';

    public function __construct(
        private AppConfig $appConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        if ($class->getName() === self::class) {
            return;
        }

        if (! $class->implements(Discovery::class)) {
            return;
        }

        $this->appConfig->discoveryClasses[] = $class->getName();
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        $directory = pathinfo(self::CACHE_PATH, PATHINFO_DIRNAME);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        file_put_contents(self::CACHE_PATH, serialize($this->appConfig->discoveryClasses));
    }

    public function restoreCache(Container $container): void
    {
        $discoveryClasses = unserialize(file_get_contents(self::CACHE_PATH), [
            'allowed_classes' => true,
        ]);

        $this->appConfig->discoveryClasses = $discoveryClasses;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
