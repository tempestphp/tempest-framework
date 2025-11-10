<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\View\Exceptions\ViewComponentPathWasInvalid;
use Tempest\View\Exceptions\ViewComponentPathWasNotFound;

final class ViewComponent
{
    public function __construct(
        public readonly string $name,
        public readonly string $contents,
        public readonly string $file,
        public readonly bool $isVendorComponent,
    ) {}

    public static function fromPath(string $path): self
    {
        $filename = pathinfo($path, PATHINFO_BASENAME);

        if (! str_starts_with($filename, 'x-') || ! str_ends_with($filename, '.view.php')) {
            throw new ViewComponentPathWasInvalid($path);
        }

        if (! is_file($path)) {
            throw new ViewComponentPathWasNotFound($path);
        }

        $name = str_replace('.view.php', '', $filename);

        $contents = file_get_contents($path);

        return new self(
            name: $name,
            contents: $contents,
            file: $path,
            isVendorComponent: str_contains($path, '/vendor/'),
        );
    }

    public bool $isProjectComponent {
        get => ! $this->isVendorComponent;
    }
}
