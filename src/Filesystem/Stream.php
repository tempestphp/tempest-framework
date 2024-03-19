<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

interface Stream
{
    public function open(StreamMode $mode = StreamMode::OPEN_OR_CREATE, StreamAccess $access = StreamAccess::READ_WRITE): void;

    public function read(int $count): string;

    public function write(string $data): int;

    public function close(): void;
}
