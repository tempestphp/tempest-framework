<?php

namespace Tempest\KeyValue\Redis;

use Predis;
use Predis\Client;
use Tempest\Container\Container;
use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;
use Tempest\Core\InsightType;
use Tempest\Support\Regex;
use Throwable;

final class RedisInsightsProvider implements InsightsProvider
{
    public string $name = 'Redis';

    public function __construct(
        private readonly Container $container,
    ) {}

    public function getInsights(): array
    {
        try {
            $redis = $this->container->get(Redis::class);
            $redis->connect();

            $version = Regex\get_match($redis->command('info', 'server'), '/redis_version:(?<version>[0-9.]+)/', match: 'version');

            return [
                'Engine' => match ($redis->getClient()::class) {
                    \Redis::class => 'Redis extension',
                    Client::class => 'Predis',
                    default => new Insight('None', InsightType::WARNING),
                },
                'Version' => $version ?: new Insight('Unknown', InsightType::WARNING),
            ];
        } catch (Throwable) {
            return [
                'Engine' => new Insight('Disconnected', InsightType::ERROR),
            ];
        }
    }
}
