<?php

declare(strict_types=1);

namespace Tempest\Vite;

final class ViteConfig
{
    /**
     * @param PrefetchConfig $prefetching Strategy for prefetching assets at runtime.
     * @param null|string $nonce The Content Security Policy nonce to apply to all generated tags.
     * @param string|false $integrityKey The key to check for integrity hashes within the manifest. Set to `false` to disable.
     * @param BuildConfig $build Configuration related to the production build.
     * @param bool $useManifestDuringTesting Whether to use the manifest during tests. `false` by default.
     */
    public function __construct(
        public PrefetchConfig $prefetching = new PrefetchConfig(),
        public BuildConfig $build = new BuildConfig(),
        public ?string $nonce = null,
        public string|false $integrityKey = 'integrity',
        public bool $useManifestDuringTesting = false,
    ) {
    }
}
