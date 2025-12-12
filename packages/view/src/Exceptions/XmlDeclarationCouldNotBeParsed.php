<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;

final class XmlDeclarationCouldNotBeParsed extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot compile views with XML declarations when PHP\'s `short_open_tag` is enabled.');
    }
}
