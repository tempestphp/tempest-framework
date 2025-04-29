<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Cache\CacheConfig;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\Enums\ConfigType;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\EventBus\EventBusConfig;
use Tempest\Log\LogConfig;
use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\View\Renderers\BladeConfig;
use Tempest\View\Renderers\TwigConfig;
use Tempest\View\ViewConfig;

use function Tempest\Support\str;

/**
 * @internal
 */
final class MakeConfigCommandTest extends FrameworkIntegrationTestCase
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

    #[DataProvider('config_type_provider')]
    #[Test]
    public function make_config(
        ConfigType $configType,
        string $expectedConfigClass,
    ): void {
        $this->console
            ->call('make:config ' . $configType->value)
            ->submit();

        $filepath = "App/{$configType->value}.config.php";

        $this->installer
            ->assertFileExists($filepath)
            ->assertFileContains($filepath, "use {$expectedConfigClass}")
            ->assertFileContains($filepath, 'return new ' . str($expectedConfigClass)->classBasename()->toString());
    }

    public static function config_type_provider(): array
    {
        return [
            'database_config' => [
                'configType' => ConfigType::DATABASE,
                'expectedConfigClass' => MysqlConfig::class,
            ],
            'twig_config' => [
                'configType' => ConfigType::TWIG,
                'expectedConfigClass' => TwigConfig::class,
            ],
            'blade_config' => [
                'configType' => ConfigType::BLADE,
                'expectedConfigClass' => BladeConfig::class,
            ],
            'view_config' => [
                'configType' => ConfigType::VIEW,
                'expectedConfigClass' => ViewConfig::class,
            ],
            'event_bus_config' => [
                'configType' => ConfigType::EVENT_BUS,
                'expectedConfigClass' => EventBusConfig::class,
            ],
            'command_bus_config' => [
                'configType' => ConfigType::COMMAND_BUS,
                'expectedConfigClass' => CommandBusConfig::class,
            ],
            'log_config' => [
                'configType' => ConfigType::LOG,
                'expectedConfigClass' => LogConfig::class,
            ],
            'cache_config' => [
                'configType' => ConfigType::CACHE,
                'expectedConfigClass' => CacheConfig::class,
            ],
            'console_config' => [
                'configType' => ConfigType::CONSOLE,
                'expectedConfigClass' => ConsoleConfig::class,
            ],
        ];
    }
}
