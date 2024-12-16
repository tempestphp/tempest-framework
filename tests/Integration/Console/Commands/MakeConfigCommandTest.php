<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Enums\ConfigType;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
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
            new ComposerNamespace('App\\', __DIR__ . '/install/App'),
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
            ->assertFileContains($filepath, "return new " . str($expectedConfigClass)->classBasename()->toString());
    }

    public static function config_type_provider(): array
    {
        return [
            'database_config' => [
                'configType' => ConfigType::DATABASE,
                'expectedConfigClass' => \Tempest\Database\DatabaseConfig::class,
            ],
            'twig_config' => [
                'configType' => ConfigType::TWIG,
                'expectedConfigClass' => \Tempest\View\Renderers\TwigConfig::class,
            ],
            'blade_config' => [
                'configType' => ConfigType::BLADE,
                'expectedConfigClass' => \Tempest\View\Renderers\BladeConfig::class,
            ],
            'view_config' => [
                'configType' => ConfigType::VIEW,
                'expectedConfigClass' => \Tempest\View\ViewConfig::class,
            ],
            'event_bus_config' => [
                'configType' => ConfigType::EVENT_BUS,
                'expectedConfigClass' => \Tempest\EventBus\EventBusConfig::class,
            ],
            'command_bus_config' => [
                'configType' => ConfigType::COMMAND_BUS,
                'expectedConfigClass' => \Tempest\CommandBus\CommandBusConfig::class,
            ],
            'log_config' => [
                'configType' => ConfigType::LOG,
                'expectedConfigClass' => \Tempest\Log\LogConfig::class,
            ],
            'cache_config' => [
                'configType' => ConfigType::CACHE,
                'expectedConfigClass' => \Tempest\Cache\CacheConfig::class,
            ],
            'console_config' => [
                'configType' => ConfigType::CONSOLE,
                'expectedConfigClass' => \Tempest\Console\ConsoleConfig::class,
            ],
        ];
    }
}
