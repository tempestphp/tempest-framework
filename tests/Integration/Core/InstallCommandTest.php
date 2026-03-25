<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class InstallCommandTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->installer
            ->configure($this->internalStorage . '/install', new Psr4Namespace('App\\', $this->internalStorage . '/install/App'))
            ->setRoot($this->internalStorage . '/install');
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        $this->installer->clean();
    }

    #[Test]
    public function class_is_adjusted(): void
    {
        $this->console
            ->call('install test --force');

        $this->installer
            ->assertFileExists(
                path: 'App/Foo/Bar/TestInstallerClass.php',
            )
            ->assertFileNotContains(
                path: 'App/Foo/Bar/TestInstallerClass.php',
                search: 'SkipDiscovery',
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
