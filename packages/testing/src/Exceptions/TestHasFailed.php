<?php

namespace Tempest\Testing\Exceptions;

use Exception;

final class TestHasFailed extends Exception implements TestException
{
    public string $reason;
    public string $location;

    public function __construct(
        string $reason,
        mixed ...$data,
    ) {
        $parsedData = [];

        foreach ($data as $value) {
            $parsedData[] = '`' . $this->export($value) . '`';
        }

        $this->reason = sprintf($reason, ...$parsedData);

        $trace = $this->getTrace();

        foreach ($this->getTrace() as $key => $traceEntry) {
            if (str_starts_with($trace[$key + 1]['class'] ?? null, 'Tempest\Testing\Testers\Tester')) {
                continue;
            }

            $this->location = sprintf('%s:%d', $traceEntry['file'], $traceEntry['line']);

            break;
        }

        parent::__construct($this->reason);
    }

    private function export(mixed $value): string
    {
        if (is_object($value)) {
            return $value::class;
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_resource($value)) {
            return 'resource';
        }

        return var_export($value, true);
    }
}
