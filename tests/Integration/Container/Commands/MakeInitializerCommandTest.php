<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Container\Commands;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
class MakeInitializerCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new Psr4Namespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[DataProvider('command_input_provider')]
    #[Test]
    public function make_command(
        string $commandArgs,
        string $expectedPath,
        string $expectedNamespace,
    ): void {
        $this->console
            ->call("make:initializer {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileNotContains($expectedPath, 'SkipDiscovery')
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';')
            ->assertFileExists($expectedPath, 'implements Initializer')
            ->assertFileContains($expectedPath, 'public function initialize');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'BookInitializer',
                'expectedPath' => 'App/BookInitializer.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Initializers\\BookInitializer',
                'expectedPath' => 'App/Initializers/BookInitializer.php',
                'expectedNamespace' => 'App\\Initializers',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Initializers/BookInitializer',
                'expectedPath' => 'App/Initializers/BookInitializer.php',
                'expectedNamespace' => 'App\\Initializers',
            ],
        ];
    }

    #[Test]
    public function make_singleton_command(): void
    {
        $this->console
            ->call('make:initializer BookInitializer --singleton')
            ->submit();

        $this->installer
            ->assertFileExists('App/BookInitializer.php')
            ->assertFileContains('App/BookInitializer.php', 'namespace App;')
            ->assertFileContains('App/BookInitializer.php', 'implements Initializer')
            ->assertFileContains('App/BookInitializer.php', 'public function initialize')
            ->assertFileContains('App/BookInitializer.php', 'use Tempest\Container\Singleton')
            ->assertFileContains('App/BookInitializer.php', '#[Singleton]');
    }
}
