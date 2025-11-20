<?php

namespace Tempest\Testing\Exceptions;

use Exception;

final class TestHasFailed extends Exception implements TestException
{
    public string $reason;

    public function __construct(
        string $reason,
        mixed ...$data,
    ) {
        foreach ($data as $key => $value) {
            $data[$key] = var_export($value, true);
        }

        $this->reason = sprintf($reason, ...$data);

        parent::__construct($this->reason);
    }

    public string $location {
        get {
            $trace = $this->getTrace()[0];

            return sprintf('%s:%d', $trace['file'], $trace['line']);
        }
    }
}