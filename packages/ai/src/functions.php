<?php

declare(strict_types=1);

namespace Tempest\AI;

use function Tempest\get;

/**
 * Get the AI chat instance.
 */
function ai(): AIChat
{
    return get(AIChat::class);
}

/**
 * Send a quick prompt to the AI.
 */
function prompt(string $prompt): AIResponse
{
    return ai()->prompt($prompt);
}
