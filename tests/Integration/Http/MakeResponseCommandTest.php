<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Support\Namespace\Psr4Namespace;

/**
 * @internal
 */
final class MakeResponseCommandTest extends FrameworkIntegrationTestCase
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
            ->call("make:response {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';')
            ->assertFileContains($expectedPath, 'implements Response')
            ->assertFileContains($expectedPath, 'use IsResponse');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'BookCreatedResponse',
                'expectedPath' => 'App/BookCreatedResponse.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Responses\\BookCreatedResponse',
                'expectedPath' => 'App/Responses/BookCreatedResponse.php',
                'expectedNamespace' => 'App\\Responses',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Responses/BookCreatedResponse',
                'expectedPath' => 'App/Responses/BookCreatedResponse.php',
                'expectedNamespace' => 'App\\Responses',
            ],
        ];
    }
}
