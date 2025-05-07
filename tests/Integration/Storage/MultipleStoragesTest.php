<?php

namespace Tests\Tempest\Integration\Storage;

use Tempest\Storage\Config\LocalStorageConfig;
use Tempest\Storage\Storage;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\Filesystem\exists;

final class MultipleStoragesTest extends FrameworkIntegrationTestCase
{
    private string $fixtures = __DIR__ . '/Fixtures/';

    protected function tearDown(): void
    {
        parent::tearDown();

        Filesystem\delete_directory($this->fixtures);
    }

    public function test_basic(): void
    {
        $this->container->config(new LocalStorageConfig(
            path: $this->fixtures . '/storage1',
            tag: 'storage1',
        ));

        $this->container->config(new LocalStorageConfig(
            path: $this->fixtures . '/storage2',
            tag: 'storage2',
        ));

        $storage1 = $this->container->get(Storage::class, tag: 'storage1');
        $storage1->write('test1.txt', '');

        $storage2 = $this->container->get(Storage::class, tag: 'storage2');
        $storage2->write('test2.txt', '');

        $this->assertTrue(exists($this->fixtures . '/storage1/test1.txt'));
        $this->assertTrue(exists($this->fixtures . '/storage2/test2.txt'));
    }
}
