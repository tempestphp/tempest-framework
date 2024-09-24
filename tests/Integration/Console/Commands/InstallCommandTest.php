<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Support\PathHelper;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class InstallCommandTest extends FrameworkIntegrationTestCase
{
    private const string INSTALL_DIR = '/tmp/';

    protected function setUp(): void
    {
        parent::setUp();

        @mkdir($this->installDir());
        chdir($this->installDir());
    }

    protected function tearDown(): void
    {
        $this->deleteInstallDir();

        parent::tearDown();
    }

    public function test_it_asks_to_continue_installing(): void
    {
        // Act
        $output = $this->console->call('install');

        // Assert
        $output->assertContains("Installing Tempest in {$this->installDir()}, continue?");
    }

    public function test_it_can_force_install(): void
    {
        // Act
        $output = $this->console->call('install --force');

        // Assert
        $output->assertDoesNotContain("Installing Tempest in {$this->installDir()}, continue?");
        $output->assertSee("{$this->installDir('/tempest')} created");
        $output->assertSee("{$this->installDir('/public/index.php')} created");
        $output->assertSee("{$this->installDir('/.env.example')} created");
        $output->assertSee("{$this->installDir('/.env')} created");

        $this->assertFileEquals($this->baseDir('/src/Tempest/Console/bin/tempest'), $this->installDir('/tempest'));
        $this->assertFileEquals($this->baseDir('/src/Tempest/Console/src/Commands/index.php'), $this->installDir('/public/index.php'));
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertTrue(is_executable($this->installDir('/tempest')));
        }

        $this->assertFileEquals($this->baseDir('/.env.example'), $this->installDir('/.env.example'));
        $this->assertFileEquals($this->baseDir('/.env.example'), $this->installDir('/.env'));
    }

    public function test_it_does_not_overwrite_files(): void
    {
        // Arrange
        @mkdir($this->installDir('/public'));
        file_put_contents($this->installDir('/tempest'), 'foo');
        file_put_contents($this->installDir('/public/index.php'), 'foo');
        file_put_contents($this->installDir('/.env.example'), 'foo');
        file_put_contents($this->installDir('/.env'), 'foo');

        // Act
        $output = $this->console->call('install --force');

        //Assert
        $output->assertSee("{$this->installDir('/tempest')} already exists, skipped.");
        $output->assertSee("{$this->installDir('/public/index.php')} already exists, skipped.");
        $output->assertSee("{$this->installDir('/.env.example')} already exists, skipped.");
        $output->assertSee("{$this->installDir('/.env')} already exists, skipped.");

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
