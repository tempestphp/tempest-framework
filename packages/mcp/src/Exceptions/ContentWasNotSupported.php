<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;

final class ContentWasNotSupported extends Exception implements McpException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function becauseBlobsCannotBeUsedOutsideResources(): self
    {
        return new self('Blob content can only be used in resource results.');
    }

    public static function becauseResourceLinksCannotBeUsedInResources(): self
    {
        return new self('Resource link content cannot be used in resource results.');
    }
}
