<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

final readonly class TwigConfig
{
    /**
     * @see \Twig\Environment::__construct()
     */
    public function __construct(
        public array $viewPaths = [],
        public ?string $cachePath = null,
        public bool $debug = false,
        public string $charset = 'utf-8',
        public bool $strictVariables = false,
        public string $autoescape = 'html',
        public ?bool $autoReload = null,
        public int $optimizations = -1,
    ) {
    }
}
