<?php

namespace Tempest\Intl\MessageFormat\Formatter;

use Tempest\Core\ProvidesContext;

final class FormattingException extends \Exception implements ProvidesContext
{
    public function __construct(
        string $message,
        public readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    public function context(): array
    {
        return $this->context;
    }
}
