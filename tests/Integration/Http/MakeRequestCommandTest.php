<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MakeRequestCommandTest extends FrameworkIntegrationTestCase
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
            ->call("make:request {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'BookRequest',
                'expectedPath' => 'App/BookRequest.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Requests\\BookRequest',
                'expectedPath' => 'App/Requests/BookRequest.php',
                'expectedNamespace' => 'App\\Requests',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Requests/BookRequest',
                'expectedPath' => 'App/Requests/BookRequest.php',
                'expectedNamespace' => 'App\\Requests',
            ],
        ];
    }
}
