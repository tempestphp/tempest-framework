<?php

namespace Tempest\Process;

use Symfony\Component\Process\Process;

enum OutputChannel: string
{
    case OUTPUT = 'out';
    case ERROR = 'err';

    public static function fromSymfonyOutputType(string $type): self
    {
        return match ($type) {
            Process::OUT => self::OUTPUT,
            Process::ERR => self::ERROR,
        };
    }
}
