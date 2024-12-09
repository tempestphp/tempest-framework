<?php

declare(strict_types=1);

namespace Tempest\Vite\TagsResolver;

interface TagsResolver
{
    public function resolveTags(array $entrypoints): array;
}
