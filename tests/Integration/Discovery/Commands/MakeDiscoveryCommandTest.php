<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Discovery\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
class MakeDiscoveryCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App'),
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
            ->call("make:discovery {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';')
            ->assertFileExists($expectedPath, 'implements Discovery')
            ->assertFileContains($expectedPath, 'public function discover');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'CustomDiscovery',
                'expectedPath' => 'App/CustomDiscovery.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'CustomDiscoveries\\CustomDiscovery',
                'expectedPath' => 'App/CustomDiscoveries/CustomDiscovery.php',
                'expectedNamespace' => 'App\\CustomDiscoveries',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'CustomDiscoveries/CustomDiscovery',
                'expectedPath' => 'App/CustomDiscoveries/CustomDiscovery.php',
                'expectedNamespace' => 'App\\CustomDiscoveries',
            ],
        ];
    }
}
