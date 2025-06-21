<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node;

final class ParsingException extends \Exception
{
    public function __construct(
        string $message,
        public readonly int $position,
    ) {
        parent::__construct("$message at position $position");
    }
}
