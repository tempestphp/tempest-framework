<?php

namespace Tempest\Process\Exceptions;

use Exception;
use Symfony\Component\Process\Exception\ProcessTimedOutException as SymfonyProcessTimedOutException;
use Tempest\Process\ProcessResult;

final class ProcessHasTimedOut extends Exception implements ProcessException
{
    public function __construct(
        public readonly ProcessResult $result,
        public readonly SymfonyProcessTimedOutException $original,
    ) {
        parent::__construct($original->getMessage(), $original->getCode(), $original);
    }
}
