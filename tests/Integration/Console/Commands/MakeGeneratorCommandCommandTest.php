<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Cache\CacheConfig;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\Enums\ConfigType;
use Tempest\Core\ComposerNamespace;
use Tempest\Database\Config\MysqlConfig;
use Tempest\EventBus\EventBusConfig;
use Tempest\Log\LogConfig;
use Tempest\View\Renderers\BladeConfig;
use Tempest\View\Renderers\TwigConfig;
use Tempest\View\ViewConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\get;
use function Tempest\Support\str;

/**
 * @internal
 */
final class MakeGeneratorCommandCommandTest extends FrameworkIntegrationTestCase
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
