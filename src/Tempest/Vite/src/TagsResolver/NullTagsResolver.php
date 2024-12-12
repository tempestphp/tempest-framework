<?php

declare(strict_types=1);

namespace Tempest\Vite\TagsResolver;

final class NullTagsResolver implements TagsResolver
{
    public function resolveTags(array $entrypoints): array
    {
        return [];
    }
}
