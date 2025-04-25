<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class MakeGeneratorCommandCommandTest extends FrameworkIntegrationTestCase
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
            ->call("make:generator-command {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'MyCommand',
                'expectedPath' => 'App/MyCommand.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Commands\\MyCommand',
                'expectedPath' => 'App/Commands/MyCommand.php',
                'expectedNamespace' => 'App\\Commands',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Commands/MyCommand',
                'expectedPath' => 'App/Commands/MyCommand.php',
                'expectedNamespace' => 'App\\Commands',
            ],
        ];
    }
}
