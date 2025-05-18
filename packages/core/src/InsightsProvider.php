<?php

namespace Tempest\Core;

/**
 * Provide insights on the current state of the application.
 */
interface InsightsProvider
{
    /**
     * Display name of this provider.
     */
    public string $name {
        get;
    }

    /**
     * Gets insights in the form of key/value pairs.
     *
     * @return array<string,mixed>
     */
    public function getInsights(): array;
}
