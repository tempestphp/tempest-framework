<?php

namespace Tempest\Core;

interface ProvidesContext
{
    /**
     * Provides context for debugging.
     */
    public function context(): iterable;
}
