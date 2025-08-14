<?php

namespace Tests\Tempest\Integration\Vite;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Commands\InstallCommand;
use Tempest\Support\JavaScript\DependencyInstaller;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ViteInstallerTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->installer->configure(__DIR__ . '/install', new Psr4Namespace('App\\', __DIR__ . '/install/app'));

        // force usage of npm because bun will mutate Tempest's root install otherwise
        touch(__DIR__ . '/install/package-lock.json');
    }

    #[After]
    protected function after(): void
    {
        $this->installer->clean();
    }

    #[Test]
    public function intalls_vite(): void
    {
        $this->console->call(InstallCommand::class, ['vite', '--force']);

        $this->installer->assertFileExists('vite.config.ts');
        $this->installer->assertFileExists('app/main.entrypoint.ts');
        $this->installer->assertFileExists('app/main.entrypoint.css');

        $this->installer->assertFileContains('package.json', '"vite"');
        $this->installer->assertFileContains('package.json', '"vite build"');
    }

    #[Test]
    public function intalls_tailwindcss(): void
    {
        $this->console->call(InstallCommand::class, ['vite', 'tailwindcss', '--force']);

        $this->installer->assertFileExists('app/main.entrypoint.ts');

        $this->installer->assertFileContains('app/main.entrypoint.css', '@import');
        $this->installer->assertFileContains('package.json', ['"vite"', '"vite build"']);
        $this->installer->assertFileContains('vite.config.ts', 'vite-plugin-tempest');
    }
}
