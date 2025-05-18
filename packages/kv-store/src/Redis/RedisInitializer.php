<?php

namespace Tempest\KeyValue\Redis;

use Predis;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventBus;
use Tempest\KeyValue\Redis\Config\PhpRedisConfig;
use Tempest\KeyValue\Redis\Config\PredisConfig;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\Reflection\ClassReflector;

final class RedisInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        return $class->getType()->matches(Redis::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, ?string $tag, Container $container): Redis
    {
        $config = $container->get(RedisConfig::class, $tag);
        $bus = $container->get(EventBus::class);

        return match ($config::class) {
            PredisConfig::class => new PredisClient($this->buildPredisClient($config), $bus),
            PhpRedisConfig::class => new PhpRedisClient($this->buildPhpRedisClient($config), $config, $bus),
        };
    }

    private function buildPhpRedisClient(PhpRedisConfig $config): \Redis
    {
        if (! extension_loaded('redis') || ! class_exists(\Redis::class)) {
            throw new MissingRedisException(Redis::class);
        }

        return new \Redis();
    }

    private function buildPredisClient(PredisConfig $config): Predis\Client
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
