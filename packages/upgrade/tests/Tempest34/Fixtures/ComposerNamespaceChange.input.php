<?php

use Tempest\Core\Composer;

final class ComposerNamespaceChange
{
    public function __construct(
        private Composer $composer,
    ) {}
}
