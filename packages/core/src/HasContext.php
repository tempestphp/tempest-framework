<?php

namespace Tempest\Core;

interface HasContext
{
    /**
     * Provides context for the exception-handling pipeline.
     */
    public function context(): iterable;
}
