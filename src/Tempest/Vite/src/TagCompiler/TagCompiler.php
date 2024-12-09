<?php

declare(strict_types=1);

namespace Tempest\Vite\TagCompiler;

use Tempest\Vite\Manifest\Chunk;

interface TagCompiler
{
    public function compileScriptTag(string $url, ?Chunk $chunk = null): string;

    public function compileStyleTag(string $url, ?Chunk $chunk = null): string;

    public function compilePreloadTag(string $url, ?Chunk $chunk = null): string;
}
