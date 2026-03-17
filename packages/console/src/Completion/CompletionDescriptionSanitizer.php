<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionDescriptionSanitizer
{
    public function sanitize(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($description));

        if (! is_array($parts) || $parts === [] || $parts[0] === '') {
            return null;
        }

        return implode(' ', $parts);
    }
}
