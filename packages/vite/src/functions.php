<?php

declare(strict_types=1);

namespace Tempest {
    use Tempest\Support\Html\HtmlString;
    use Tempest\Vite\Vite;

    /**
     * Inject tags for the specified or configured `$entrypoints`.
     */
    function vite_tags(null|string|array $entrypoints = null): HtmlString
    {
        return new HtmlString(
            string: implode('', get(Vite::class)->getTags(is_array($entrypoints) ? $entrypoints : [$entrypoints])),
        );
    }
}
