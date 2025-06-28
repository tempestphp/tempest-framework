<?php

declare(strict_types=1);

namespace Tempest\Generation\Exceptions;

use Exception;

final class FileGenerationWasAborted extends Exception implements FileGenerationException
{
}
