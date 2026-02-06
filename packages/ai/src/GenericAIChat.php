<?php

declare(strict_types=1);

namespace Tempest\AI;

final class GenericAIChat implements AIChat
{
    private ?string $model = null;
    private ?float $temperature = null;
    private ?int $maxTokens = null;
    private ?string $systemPrompt = null;
    private ?AIProvider $provider = null;

    /** @var array<string, AIDriver> */
    private array $drivers = [];

    public function __construct(
        private readonly AIConfig $config,
    ) {}

    public function addDriver(AIDriver $driver): self
    {
        $this->drivers[$driver->getProvider()->value] = $driver;

        return $this;
    }

    public function prompt(string $prompt): AIResponse
    {
        $messages = [];

        if ($this->systemPrompt !== null) {
            $messages[] = AIMessage::system($this->systemPrompt);
        }

        $messages[] = AIMessage::user($prompt);

        return $this->chat($messages);
    }

    public function chat(array $messages): AIResponse
    {
        $provider = $this->provider ?? $this->config->defaultProvider;
        $driver = $this->getDriver($provider);

        return $driver->chat(
            messages: $messages,
            model: $this->model,
            temperature: $this->temperature,
            maxTokens: $this->maxTokens,
        );
    }

    public function withModel(string $model): self
    {
        $clone = clone $this;
        $clone->model = $model;

        return $clone;
    }

    public function withTemperature(float $temperature): self
    {
        $clone = clone $this;
        $clone->temperature = $temperature;

        return $clone;
    }

    public function withMaxTokens(int $maxTokens): self
    {
        $clone = clone $this;
        $clone->maxTokens = $maxTokens;

        return $clone;
    }

    public function withSystemPrompt(string $systemPrompt): self
    {
        $clone = clone $this;
        $clone->systemPrompt = $systemPrompt;

        return $clone;
    }

    public function using(AIProvider $provider): self
    {
        $clone = clone $this;
        $clone->provider = $provider;

        return $clone;
    }

    private function getDriver(AIProvider $provider): AIDriver
    {
        if (! isset($this->drivers[$provider->value])) {
            throw new Exception\AIException("No driver configured for provider: {$provider->value}");
        }

        return $this->drivers[$provider->value];
    }
}
