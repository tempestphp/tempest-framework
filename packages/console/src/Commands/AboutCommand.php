<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Stringable;
use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;
use Tempest\Support\Arr;
use Tempest\Support\Json;
use Tempest\Support\Str;

use function Tempest\Support\arr;

final readonly class AboutCommand
{
    public function __construct(
        private readonly Console $console,
        private readonly Container $container,
        private readonly AppConfig $appConfig,
    ) {}

    #[ConsoleCommand(name: 'about', description: 'Shows insights about the application', aliases: ['insights'])]
    public function __invoke(
        #[ConsoleArgument(description: 'Formats the outpuyt to JSON', aliases: ['--json'])]
        ?bool $json = null,
    ): ExitCode {
        if ($json) {
            $this->writeInsightsAsJson();
        } else {
            $this->writeFormattedInsights();
        }

        return ExitCode::SUCCESS;
    }

    private function writeFormattedInsights(): void
    {
        foreach ($this->appConfig->insightsProviders as $class) {
            /** @var InsightsProvider $provider */
            $provider = $this->container->get($class);

            $this->console->header($provider->name);

            foreach ($provider->getInsights() as $key => $value) {
                $this->console->keyValue($key, $this->formatInsight($value));
            }
        }
    }

    private function writeInsightsAsJson(): void
    {
        $json = [];

        foreach ($this->appConfig->insightsProviders as $class) {
            /** @var InsightsProvider $provider */
            $provider = $this->container->get($class);

            $json[Str\to_snake_case($provider->name)] = Arr\map_with_keys(
                array: $provider->getInsights(),
                map: fn (mixed $value, string $key) => yield Str\to_snake_case($key) => $this->rawInsight($value),
            );
        }

        $this->console->writeRaw(Json\encode($json));
    }

    private function formatInsight(Stringable|Insight|array|string $value): string
    {
        return arr($value)
            ->filter()
            ->map(function (Stringable|Insight|string $value) {
                if ($value instanceof Insight) {
                    return $value->formattedValue;
                }

                return (string) $value;
            })
            ->implode(', ')
            ->toString();
    }

    private function rawInsight(Stringable|Insight|array|string $value): array
    {
        return arr($value)
            ->filter()
            ->map(function (Stringable|Insight|string $value) {
                if ($value instanceof Insight) {
                    return $value->value;
                }

                return Str\strip_tags((string) $value);
            })
            ->toArray();
    }
}
