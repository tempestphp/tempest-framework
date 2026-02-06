<?php

declare(strict_types=1);

namespace Tempest\AI;

interface AIChat
{
    /**
     * Send a simple prompt and get a response.
     */
    public function prompt(string $prompt): AIResponse;

    /**
     * Send multiple messages as a conversation.
     *
     * @param AIMessage[] $messages
     */
    public function chat(array $messages): AIResponse;

    /**
     * Set the model to use for this request.
     */
    public function withModel(string $model): self;

    /**
     * Set the temperature for this request.
     */
    public function withTemperature(float $temperature): self;

    /**
     * Set the max tokens for this request.
     */
    public function withMaxTokens(int $maxTokens): self;

    /**
     * Set a system prompt for this request.
     */
    public function withSystemPrompt(string $systemPrompt): self;

    /**
     * Use a specific AI provider.
     */
    public function using(AIProvider $provider): self;
}
