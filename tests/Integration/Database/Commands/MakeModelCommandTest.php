<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
class MakeModelCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App')
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('command_input_provider')]
    public function make_command(
        string $commandArgs,
        string $expectedPath,
        string $expectedNamespace
    ): void {
        $this->console
            ->call("make:model {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'Book',
                'expectedPath' => 'App/Book.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Books\\BookModel',
                'expectedPath' => 'App/Books/BookModel.php',
                'expectedNamespace' => 'App\\Books',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Books/BookModel',
                'expectedPath' => 'App/Books/BookModel.php',
                'expectedNamespace' => 'App\\Books',
            ],
        ];
    }
}
