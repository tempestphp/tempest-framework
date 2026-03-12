<?php

declare(strict_types=1);

namespace Tempest\Generation\Php\Exceptions;

use Exception;

final class FileGenerationWasAborted extends Exception implements FileGenerationException
{
}
