<?php

namespace Tempest\Storage\Testing;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Storage\Storage;
use Tempest\Storage\StorageInitializer;
use Tempest\Support\Arr;
use UnitEnum;

use function Tempest\Support\Str\to_kebab_case;

final readonly class StorageTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Forces the usage of a testing storage. When setting `$persist` to `true`, the disk is not erased.
     */
    public function fake(null|string|UnitEnum $tag = null, bool $persist = false): TestingStorage
    {
        $storage = new TestingStorage(match (true) {
            is_string($tag) => to_kebab_case($tag),
            $tag instanceof UnitEnum => to_kebab_case($tag->name),
            default => 'default',
        });

        $this->container->singleton(Storage::class, $storage, $tag);

        return $persist
            ? $storage->createDirectory()
            : $storage->cleanDirectory();
    }

    /**
     * Prevents storage from being used without a fake.
     */
    public function preventUsageWithoutFake(): void
    {
        if (! ($this->container instanceof GenericContainer)) {
            throw new \RuntimeException('Container is not a GenericContainer, unable to prevent usage without fake.');
        }

        $this->container->unregister(Storage::class, tagged: true);
        $this->container->removeInitializer(StorageInitializer::class);
        $this->container->addInitializer(RestrictedStorageInitializer::class);
    }
}
