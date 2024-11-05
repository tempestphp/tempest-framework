<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class InstallCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                __DIR__ . '/install',
                new ComposerNamespace('App\\', __DIR__ . '/install/App'),
            )
            ->setRoot(__DIR__ . '/install');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_class_is_adjusted(): void
    {
        $this->console
            ->call('install test --force');

        $this->installer
            ->assertFileExists(
                path: 'App/Foo/Bar/TestInstallerClass.php',
            )
            ->assertFileNotContains(
                path: 'App/Foo/Bar/TestInstallerClass.php',
                search: 'DoNotDiscover',
            )
            ->assertFileContains(
                path: 'App/Foo/Bar/TestInstallerClass.php',
                search: 'namespace App\Foo\Bar;',
            )
            ->assertFileExists(
                path: 'App/View/TestInstallerFile.html',
                content: '<html></html>',
            );
    }
}
