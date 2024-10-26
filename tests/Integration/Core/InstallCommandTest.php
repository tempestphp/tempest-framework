<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Support\PathHelper;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class InstallCommandTest extends FrameworkIntegrationTestCase
{
    private const string INSTALL_DIR = '/tmp/';

    protected function setUp(): void
    {
        parent::setUp();

        @mkdir($this->installDir());
        chdir($this->installDir());
        $this->kernel->root = $this->installDir();
    }

    protected function tearDown(): void
    {
        $this->deleteInstallDir();

        parent::tearDown();
    }

    public function test_it_asks_to_continue_installing(): void
    {
        $this->console
            ->call('install framework')
            ->assertSee('Running the `framework` installer, continue?  [yes/no]');
    }

    public function test_it_can_force_install(): void
    {
        $this->console
            ->call('install framework --force')
            ->assertDoesNotContain("Running the `framework` installer, continue?  [yes/no]")
            ->assertSee("{$this->installDir('/tempest')} created")
            ->assertSee("{$this->installDir('/public/index.php')} created")
            ->assertSee("{$this->installDir('/.env.example')} created")
            ->assertSee("{$this->installDir('/.env')} created")
        ->printFormatted();

        ld('hi');
        $this->assertFileEquals(
            $this->baseDir('/src/Tempest/Framework/Installers/tempest'),
            $this->installDir('/tempest')
        );

        $this->assertFileEquals(
            $this->baseDir('/src/Tempest/Framework/Installers/index.php'),
            $this->installDir('/public/index.php')
        );

        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertTrue(is_executable($this->installDir('/tempest')));
        }

        $this->assertFileEquals($this->baseDir('/.env.example'), $this->installDir('/.env.example'));
        $this->assertFileEquals($this->baseDir('/.env.example'), $this->installDir('/.env'));
    }

    public function test_it_does_not_overwrite_files(): void
    {
        @mkdir($this->installDir('/public'));
        file_put_contents($this->installDir('/tempest'), 'foo');
        file_put_contents($this->installDir('/public/index.php'), 'foo');
        file_put_contents($this->installDir('/.env.example'), 'foo');
        file_put_contents($this->installDir('/.env'), 'foo');

        $this->console
            ->call('install framework')
            ->submit('yes')
            ->assertSee('.env.example already exists');

        $this->assertStringEqualsFile($this->installDir('/tempest'), 'foo');
        $this->assertStringEqualsFile($this->installDir('/public/index.php'), 'foo');
        $this->assertStringEqualsFile($this->installDir('/.env.example'), 'foo');
        $this->assertStringEqualsFile($this->installDir('/.env'), 'foo');
    }

    private function baseDir(string ...$paths): string
    {
        return PathHelper::make(realpath(__DIR__ . '/../../../../'), ...$paths);
    }

    private function installDir(string $path = ''): string
    {
        return $this->baseDir(static::INSTALL_DIR, $path);
    }

    private function deleteInstallDir(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->installDir(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $fileinfo->isDir()
                ? @rmdir($fileinfo->getRealPath())
                : @unlink($fileinfo->getRealPath());
        }

        @rmdir($this->installDir());
    }
}
