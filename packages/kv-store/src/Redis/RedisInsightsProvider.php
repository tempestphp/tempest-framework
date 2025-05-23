<?php

namespace Tempest\KeyValue\Redis;

use Predis;
use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;
use Tempest\Support\Regex;

final class RedisInsightsProvider implements InsightsProvider
{
    public string $name = 'Redis';

    public function __construct(
        private readonly Redis $redis,
    ) {}

    public function getInsights(): array
    {
        $client = $this->redis->getClient();

        try {
            $version = Regex\get_match($this->redis->command('info', 'server'), '/redis_version:(?<version>[0-9.]+)/', match: 'version');

            return [
                'Engine' => match (get_class($client)) {
                    \Redis::class => 'Redis extension',
                    Predis\Client::class => 'Predis',
                    default => new Insight('None', Insight::WARNING),
                },
                'Version' => $version ?: new Insight('Unknown', Insight::WARNING),
            ];
        } catch (\Throwable) {
            return [
                'Engine' => new Insight('Disconnected', Insight::ERROR),
            ];
        }
    }
}
