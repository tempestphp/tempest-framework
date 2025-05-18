<?php

namespace Tempest\Core;

use Tempest\Support\Str;

/**
 * Represents an insight for the `tempest about` command.
 */
final class Insight
{
    public const string ERROR = 'error';
    public const string SUCCESS = 'success';
    public const string NORMAL = 'normal';
    public const string WARNING = 'warning';

    public string $formattedValue {
        get => match ($this->type) {
            self::ERROR => "<style='bold fg-red'>" . mb_strtoupper($this->value) . '</style>',
            self::SUCCESS => "<style='bold fg-green'>" . mb_strtoupper($this->value) . '</style>',
            self::WARNING => "<style='bold fg-yellow'>" . mb_strtoupper($this->value) . '</style>',
            self::NORMAL => $this->value,
        };
    }

    public function __construct(
        private(set) string $value,
        private string $type = self::NORMAL,
    ) {
        $this->value = $value;
    }
}
