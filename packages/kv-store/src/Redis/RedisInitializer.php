<?php

namespace Tempest\KeyValue\Redis;

use Predis;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventBus;
use Tempest\KeyValue\Redis\Config\RedisConfig;

final class RedisInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Redis
    {
        $config = $container->get(RedisConfig::class);
        $bus = $container->get(EventBus::class);

        try {
            return new PhpRedisClient($this->buildPhpRedisClient(), $config, $bus);
        } catch (MissingRedisException) {
            return new PredisClient($this->buildPredisClient($config), $bus);
        }
    }

    private function buildPhpRedisClient(): \Redis
    {
        if (! extension_loaded('redis') || ! class_exists(\Redis::class)) {
            throw new MissingRedisException(\Redis::class);
        }

        return new \Redis();
    }

    private function buildPredisClient(RedisConfig $config): Predis\Client
    {
        if (! class_exists(Predis\Client::class)) {
            throw new MissingRedisException(Predis\Client::class);
        }

        return new Predis\Client(
            parameters: array_filter([
                'scheme' => $config->scheme->value,
                'host' => $config->host ?? '127.0.0.1',
                'port' => $config->port ?? 6379,
                'password' => $config->password,
                'username' => $config->username,
                'database' => $config->database,
                'persistent' => $config->persistent,
                'timeout' => $config->connectionTimeOut,
                ...$config->connection,
            ]),
            options: array_filter([
                'prefix' => $config->prefix,
                ...$config->options,
            ]),
        );
    }
}
