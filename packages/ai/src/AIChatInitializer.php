<?php

declare(strict_types=1);

namespace Tempest\AI;

use Tempest\AI\Driver\AnthropicDriver;
use Tempest\AI\Driver\OpenAIDriver;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\HttpClient\HttpClient;

final class AIChatInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AIChat|GenericAIChat
    {
        $config = $container->get(AIConfig::class);
        $httpClient = $container->get(HttpClient::class);

        $chat = new GenericAIChat($config);

        // Register available drivers
        $chat->addDriver(new OpenAIDriver($httpClient, $config));
        $chat->addDriver(new AnthropicDriver($httpClient, $config));

        return $chat;
    }
}
