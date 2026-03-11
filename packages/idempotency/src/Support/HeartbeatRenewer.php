<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Support;

use Tempest\Idempotency\Store\IdempotencyStore;
use Throwable;

use const SIG_DFL;
use const SIGALRM;

/**
 * Periodically refreshes the heartbeat of a pending idempotency record during long-running work when pcntl alarms are available.
 */
final class HeartbeatRenewer
{
    private bool $active = false;

    private mixed $previousHandler = SIG_DFL;

    private bool $previousAsyncSignals = false;

    private int $previousAlarmRemaining = 0;

    /**
     * Starts a recurring alarm that keeps the pending record's heartbeat fresh while work is still running.
     */
    public function start(
        IdempotencyStore $store,
        string $scope,
        string $key,
        string $owner,
        int $intervalInSeconds,
        int $recordTtlInSeconds,
    ): void {
        if ($this->active || ! self::supported()) {
            return;
        }

        $this->previousAsyncSignals = pcntl_async_signals(true);
        $this->previousHandler = pcntl_signal_get_handler(SIGALRM);

        pcntl_signal(SIGALRM, static function () use ($store, $scope, $key, $owner, $intervalInSeconds, $recordTtlInSeconds): void {
            try {
                $store->updateHeartbeat($scope, $key, $owner, time(), $recordTtlInSeconds);
            } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
            }

            pcntl_alarm($intervalInSeconds);
        });

        $this->previousAlarmRemaining = pcntl_alarm($intervalInSeconds);
        $this->active = true;
    }

    /**
     * Stops the recurring heartbeat alarm and restores any previous signal configuration.
     */
    public function stop(): void
    {
        if (! $this->active) {
            return;
        }

        pcntl_alarm(0);
        pcntl_signal(SIGALRM, $this->previousHandler);

        if ($this->previousAlarmRemaining > 0 && is_callable($this->previousHandler)) {
            pcntl_alarm($this->previousAlarmRemaining);
        }

        pcntl_async_signals($this->previousAsyncSignals);
        $this->active = false;
    }

    public static function supported(): bool
    {
        return function_exists('pcntl_alarm') && function_exists('pcntl_signal') && function_exists('pcntl_async_signals') && function_exists('pcntl_signal_get_handler');
    }
}
