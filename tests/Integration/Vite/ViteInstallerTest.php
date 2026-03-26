<?php

namespace Tests\Tempest\Integration\Vite;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Commands\InstallCommand;
use Tempest\Support\Filesystem;
use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\Vite\Installer\ViteInstaller;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ViteInstallerTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->installer
            ->configure(__DIR__ . '/install', new Psr4Namespace('App\\', __DIR__ . '/install/app'))
            ->setRoot(__DIR__ . '/install');

        // force usage of npm because bun (which will be detected by our bun.lock)
        // will mutate Tempest's root install otherwise
        Filesystem\create_directory(__DIR__ . '/install/node_modules');
        Filesystem\create_file(__DIR__ . '/install/package-lock.json');
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        $this->installer->clean();
    }

    #[Test]
    public function intalls_vite(): void
    {
        $this->console->call(InstallCommand::class, [ViteInstaller::class, '--force']);

        $this->installer->assertFileExists('vite.config.ts');
        $this->installer->assertFileExists('app/main.entrypoint.ts');

        $this->installer->assertFileContains('package.json', '"vite"');
        $this->installer->assertFileContains('package.json', '"vite build"');
    }

    #[Test]
    public function intalls_tailwindcss(): void
    {
        $this->console->call(InstallCommand::class, [ViteInstaller::class, '--tailwindcss', '--force']);

        $this->installer->assertFileExists('app/main.entrypoint.ts');

        $this->installer->assertFileContains('app/main.entrypoint.css', '@import');
        $this->installer->assertFileContains('package.json', ['"vite"', '"vite build"']);
        $this->installer->assertFileContains('vite.config.ts', 'vite-plugin-tempest');
    }
}
