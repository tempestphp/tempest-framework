<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use Closure;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Support\Filesystem;
use Tempest\Support\JavaScript\DependencyInstaller;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DependencyInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! trim(shell_exec('which bun') ?? '') || ! trim(shell_exec('which npm') ?? '')) {
            $this->markTestSkipped('This test requires the `bun` and `npm` binaries to be available.');
        }
    }

    #[TestWith(['bun.lock'])]
    #[TestWith(['package-lock.json'])]
    public function test_can_silently_install_deps(string $lockfile): void
    {
        $this->callInTemporaryDirectory(function (string $directory) use ($lockfile): void {
            file_put_contents("{$directory}/package.json", data: '{}');
            file_put_contents("{$directory}/{$lockfile}", data: null);

            $installer = $this->container->get(DependencyInstaller::class);
            $installer->silentlyInstallDependencies($directory, 'vite-plugin-tempest', dev: true);

            $this->assertTrue(is_dir($directory . '/node_modules'), message: 'Dependencies were not installed.');
            $this->assertNotNull(json_decode(file_get_contents($directory . '/package.json'), associative: true)['devDependencies']['vite-plugin-tempest']);
        });
    }

    public function test_asks_for_package_manager(): void
    {
        $this->callInTemporaryDirectory(function (string $directory): void {
            file_put_contents("{$directory}/package.json", data: '{}');

            $this->console
                ->call(function () use ($directory): void {
                    $installer = $this->container->get(DependencyInstaller::class);
                    $installer->installDependencies($directory, 'vite-plugin-tempest', dev: true);
                })
                ->submit('npm');

            $this->assertTrue(is_dir($directory . '/node_modules'), message: 'Dependencies were not installed.');
            $this->assertTrue(file_exists($directory . '/package-lock.json'), message: 'The lockfile was not created.');
            $this->assertNotNull(json_decode(file_get_contents($directory . '/package.json'), associative: true)['devDependencies']['vite-plugin-tempest']);
        });
    }

    #[TestWith(['bun.lock'])]
    #[TestWith(['package-lock.json'])]
    public function test_can_install_non_dev_dependencies(string $lockfile): void
    {
        $this->callInTemporaryDirectory(function (string $directory) use ($lockfile): void {
            file_put_contents("{$directory}/package.json", data: '{}');
            file_put_contents("{$directory}/{$lockfile}", data: null);

            $installer = $this->container->get(DependencyInstaller::class);
            $installer->silentlyInstallDependencies($directory, 'vite-plugin-tempest', dev: false);

            $this->assertTrue(is_dir($directory . '/node_modules'), message: 'Dependencies were not installed.');
            $this->assertNotNull(json_decode(file_get_contents($directory . '/package.json'), associative: true)['dependencies']['vite-plugin-tempest']);
        });
    }

    private function callInTemporaryDirectory(Closure $callback): void
    {
        $directory = __DIR__ . '/Fixtures/tmp';

        Filesystem\ensure_directory_empty($directory);
        $callback($directory);
        Filesystem\delete_directory($directory);
    }
}
