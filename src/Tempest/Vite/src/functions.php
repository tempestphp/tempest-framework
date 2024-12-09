<?php

declare(strict_types=1);

namespace Tempest {
    use Tempest\Vite\Vite;

    function vite_tags(): string
    {
        return get(Vite::class)->getTags();
    }

    function set_vite_nonce(string $nonce): void
    {
        get(Vite::class)->setNonce($nonce);
    }
}
