<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Support;

final readonly class ProcessingOwner
{
    private const string DELIMITER = '|';

    public function create(): string
    {
        $pid = getmypid();

        return sprintf(
            '%s%s%d%s%s',
            php_uname('n'),
            self::DELIMITER,
            is_int($pid) ? $pid : 0,
            self::DELIMITER,
            bin2hex(random_bytes(8)),
        );
    }

    public function resolveLiveness(?string $owner, ?int $heartbeatAt, int $staleAfterInSeconds): ProcessingOwnerLiveness
    {
        if ($owner === null) {
            return ProcessingOwnerLiveness::UNKNOWN;
        }

        $parts = explode(self::DELIMITER, $owner, 3);

        if (count($parts) !== 3) {
            return ProcessingOwnerLiveness::UNKNOWN;
        }

        [$host, $pid] = $parts;

        if (! ctype_digit($pid)) {
            return ProcessingOwnerLiveness::UNKNOWN;
        }

        $pidAsInt = (int) $pid;

        if ($pidAsInt <= 0) {
            return ProcessingOwnerLiveness::UNKNOWN;
        }

        if ($host !== php_uname('n')) {
            return $this->isHeartbeatStale($heartbeatAt, $staleAfterInSeconds)
                ? ProcessingOwnerLiveness::DEAD
                : ProcessingOwnerLiveness::UNKNOWN;
        }

        if (function_exists('posix_getpgid')) {
            return posix_getpgid($pidAsInt) !== false
                ? ProcessingOwnerLiveness::ALIVE
                : ProcessingOwnerLiveness::DEAD;
        }

        return $this->isHeartbeatStale($heartbeatAt, $staleAfterInSeconds)
            ? ProcessingOwnerLiveness::DEAD
            : ProcessingOwnerLiveness::UNKNOWN;
    }

    private function isHeartbeatStale(?int $heartbeatAt, int $staleAfterInSeconds): bool
    {
        if ($heartbeatAt === null) {
            return false;
        }

        return (time() - $heartbeatAt) >= max(1, $staleAfterInSeconds);
    }
}
