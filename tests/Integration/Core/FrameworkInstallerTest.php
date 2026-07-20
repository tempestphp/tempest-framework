<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class FrameworkInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                $this->internalStorage . '/install',
                new Psr4Namespace('App\\', $this->internalStorage . '/install/App'),
            )
            ->setRoot($this->internalStorage . '/install');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    public function it_can_force_install(): void
    {
        $this->console
            ->call('install framework --force')
            ->assertDoesNotContain('Running the framework installer, continue?')
            ->assertContains('Generating full discovery cache')
            ->assertContains('Signing key generated successfully');

        $this->installer
            ->assertFileExists(
                path: 'tempest',
                content: file_get_contents(__DIR__ . '/../../../src/Tempest/Framework/Installers/tempest'),
            )
            ->assertFileExists(
                path: 'public/index.php',
                content: file_get_contents(__DIR__ . '/../../../src/Tempest/Framework/Installers/index.php'),
            )
            ->assertFileExists(
                path: '.env.example',
                content: file_get_contents(__DIR__ . '/../../../.env.example'),
            )
            ->assertFileExists(
                path: '.env',
                content: file_get_contents(__DIR__ . '/../../../.env.example'),
            )
            ->assertFileExists(
                path: 'AGENTS.md',
                content: file_get_contents(__DIR__ . '/../../../src/Tempest/Framework/Installers/AGENTS.md'),
            )
            ->assertCommandExecuted('composer up');

        if (PHP_OS_FAMILY !== 'Windows') {
            $this->assertTrue(is_executable($this->installer->path('tempest')));

            $this->installer
                ->assertFileExists(
                    path: 'CLAUDE.md',
                    content: file_get_contents(__DIR__ . '/../../../src/Tempest/Framework/Installers/AGENTS.md'),
                );
        }
    }

    #[Test]
    public function it_does_not_overwrite_files(): void
    {
        $this->installer->put('/tempest', 'foo');
        $this->installer->put('/public/index.php', 'foo');
        $this->installer->put('/.env.example', 'foo');
        $this->installer->put('/.env', 'foo');

        $this->console
            ->call('install framework')
            ->submit('yes');

        $this->assertStringEqualsFile($this->installer->path('/tempest'), 'foo');
        $this->assertStringEqualsFile($this->installer->path('/public/index.php'), 'foo');
        $this->assertStringEqualsFile($this->installer->path('/.env.example'), 'foo');
        $this->assertStringEqualsFile($this->installer->path('/.env'), 'foo');
    }
}
