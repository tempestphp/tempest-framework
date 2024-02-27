<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use ReflectionParameter;
use Tempest\Container\ContainerLog;
use Tempest\Container\ContainerLogItem;

final class CannotAutowireException extends Exception
{
    public function __construct(ContainerLog $log, ReflectionParameter $origin)
    {
        $log->add(new ContainerLogItem(id: '_', subject: $origin));

        $message = PHP_EOL. PHP_EOL;

        $message .= 'Cannot autowire';

        $message .= $log;

        $message .= PHP_EOL;

        parent::__construct(
            message: $message,
        );
    }
}
