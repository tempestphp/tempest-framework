<?php

use Tempest\Core\ComposerJsonCouldNotBeLocated;

final class ComposerJsonCouldNotBeLocatedNamespaceChange
{
    public function handle(): void
    {
        throw new ComposerJsonCouldNotBeLocated();
    }
}
