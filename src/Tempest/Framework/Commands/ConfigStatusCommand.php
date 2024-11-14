<?php

declare(strict_types=1);

namespace Tempest\Framework\Commands;

use function file_get_contents;
use function function_exists;
use function is_array;
use function is_object;
use function realpath;
use function str_contains;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\HasConsole;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Highlight\Languages\Json\JsonLanguage;
use Tempest\Highlight\Languages\Php\PhpLanguage;
use Tempest\Reflection\ClassReflector;
use function var_export;

final readonly class ConfigStatusCommand
{
    use HasConsole;
    private const int MAX_JSON_DEPTH = 32;

    public function __construct(
        private LoadConfig $loadConfig,
    ) {
    }

    #[ConsoleCommand(name: 'config:status', description: 'Show resolved configuration')]
    public function __invoke(
        ConfigStatusFormat $format = ConfigStatusFormat::JSON,
        ?string $filter = null,
    ): ExitCode {
        $configs = $this->resolveConfig($filter);

        if (empty($configs)) {
            $this->console->error('No configuration found');

            return ExitCode::ERROR;
        }

        match ($format) {
            ConfigStatusFormat::DUMP => $this->dump($configs),
            ConfigStatusFormat::JSON => $this->json($configs),
            ConfigStatusFormat::FILE => $this->file($configs),
        };

        return ExitCode::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveConfig(?string $filter): array
    {
        $configPaths = $this->loadConfig->load();
        $configs = [];
        $uniqueMap = [];

        foreach ($configPaths as $configPath) {
            $config = require $configPath;
            $configPath = realpath($configPath);

            if (
                $filter === null
                || str_contains($configPath, $filter)
                || str_contains($config::class, $filter)
            ) {
                $configs[$configPath] = $config;
                $uniqueMap[$config::class] = $configPath;
            }
        }

        // LoadConfig::load() returns all config paths
        // that are overwritten by container in their order
        $resolvedConfigs = [];

        foreach ($uniqueMap as $configPath) {
            $resolvedConfigs[$configPath] = $configs[$configPath];
        }

        return $resolvedConfigs;
    }

    /**
     * @param array<string, mixed> $configs
     */
    private function dump(array $configs): void
    {
        if (function_exists('lw')) {
            lw($configs);

            return;
        }

        $this->console->writeln(var_export($configs, true));
    }

    /**
     * @param array<string, mixed> $configs
     */
    private function json(array $configs): void
    {
        $formatted = $this->formatForJson($configs);

        $this->console->writeWithLanguage(
            json_encode(
                $formatted,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            new JsonLanguage(),
        );
    }

    private function formatForJson(mixed $value, int $depth = 0): mixed
    {
        if ($depth > self::MAX_JSON_DEPTH) {
            return '@...';
        }

        if (is_object($value)) {
            $result = [
                '@type' => $value::class,
            ];

            $reflector = new ClassReflector($value);

            foreach ($reflector->getProperties() as $property) {
                $result[$property->getName()] = $this->formatForJson($property->getValue($value), $depth + 1);
            }

            return $result;
        }

        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->formatForJson($item, $depth + 1);
            }

            return $result;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $configs
     */
    private function file(array $configs): void
    {
        $phpLanguage = new PhpLanguage();

        foreach (array_keys($configs) as $path) {
            $this->console->writeln("<em>{$path}</em>");
            $this->console->writeWithLanguage(
                file_get_contents($path),
                $phpLanguage,
            );
            $this->console->writeln();
        }
    }
}
