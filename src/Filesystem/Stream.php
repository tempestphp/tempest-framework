<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

interface Stream
{
    public function open(): void;

    public function read(int $count): string;

    public function write(string $data): int;

    public function close(): void;
}
