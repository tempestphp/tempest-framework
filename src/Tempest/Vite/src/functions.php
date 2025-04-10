<?php

declare(strict_types=1);

namespace Tempest {
    use Tempest\Support\Html\HtmlString;
    use Tempest\Vite\Vite;
    use UnitEnum;

    /**
     * Inject tags for the specified or configured `$entrypoints`.
     */
    function vite_tags(null|string|array $entrypoints = null, null|string|UnitEnum $tag = null): HtmlString
    {
        return new HtmlString(
            string: implode('', get(Vite::class, $tag)->getTags(is_array($entrypoints) ? $entrypoints : [$entrypoints])),
        );
    }
}
