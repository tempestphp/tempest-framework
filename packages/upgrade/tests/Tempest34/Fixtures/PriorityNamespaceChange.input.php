<?php

use Tempest\Core\Priority;

final class PriorityNamespaceChange
{
    public function __construct(
        private Priority $priority,
    ) {}
}
