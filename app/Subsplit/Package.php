<?php

namespace App\Subsplit;

use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\arr;

final class Package
{
    public function __construct(
        public readonly string $path,
        public readonly string $name,
    ) {}

    public string $remote {
        get => "git@github.com:tempestphp/tempest-{$this->name}.git";
    }

    public string $buildPath {
        get => __DIR__ . '/../../build/packages/' . $this->name . '/';
    }

    public string $sourcePath {
        get => __DIR__ . '/../../packages/' . $this->name . '/';
    }

    /** @return ImmutableArray<array-key, self> */
    public static function all(): ImmutableArray
    {
        return arr(glob(__DIR__ . '/../../packages/*'))
            ->map(fn (string $path) => self::fromPath($path));
    }

    public static function fromPath(string $path): self
    {
        return new self(
            path: $path,
            name: pathinfo($path, PATHINFO_FILENAME),
        );
    }
}