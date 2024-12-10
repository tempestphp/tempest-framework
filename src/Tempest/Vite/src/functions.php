<?php

declare(strict_types=1);

namespace Tempest {
    use Tempest\Vite\Vite;

    function vite_tags(?array $entrypoints = null): string
    {
        return get(Vite::class)->getTags($entrypoints);
    }

    function set_vite_nonce(string $nonce): void
    {
        get(Vite::class)->setNonce($nonce);
    }
}
