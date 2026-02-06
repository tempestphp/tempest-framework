<?php

declare(strict_types=1);

namespace Tempest\AI;

interface AIDriver
{
    /**
     * Send messages to the AI provider and get a response.
     *
     * @param AIMessage[] $messages
     */
    public function chat(
        array $messages,
        ?string $model = null,
        ?float $temperature = null,
        ?int $maxTokens = null,
    ): AIResponse;

    /**
     * Get the provider this driver handles.
     */
    public function getProvider(): AIProvider;
}
