<?php

namespace Tests\Tempest\Integration\Auth;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tempest\Core\Kernel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AuthInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $installDir = __DIR__ . '/install';

        if (! is_dir($installDir)) {
            mkdir($installDir);

        }
        $this->container->get(Kernel::class)->root = $installDir;
        $this->container->get(Composer::class)->setMainNamespace(new ComposerNamespace('App\\', $installDir));
    }

    protected function tearDown(): void
    {
        $installDir = __DIR__ . '/install';

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($installDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $fileinfo->isDir()
                ? @rmdir($fileinfo->getRealPath())
                : @unlink($fileinfo->getRealPath());
        }

        @rmdir($installDir);

        parent::tearDown();
    }

    public function test_install_auth(): void
    {
        $this->console->call('install auth --force');

        $publishItems = [
            'User',
            'UserMigration',
            'Permission',
            'PermissionMigration',
            'UserPermission',
            'UserPermissionMigrations',
        ];

        foreach ($publishItems as $publishItem) {
            $this->assertFileExists(__DIR__ . "/install/{$publishItem}.php");
            $file = file_get_contents(__DIR__ . "/install/{$publishItem}.php");
            $this->assertStringContainsString('namespace App;', $file);
            $this->assertStringNotContainsString('DoNotDiscover', $file);
        }
    }
}