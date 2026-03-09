<?php

declare(strict_types=1);

namespace Tempest\Core;

final class Insight
{
    public string $formattedValue {
        get => match ($this->type) {
            InsightType::ERROR => "<style='bold fg-red'>" . mb_strtoupper($this->value) . '</style>',
            InsightType::SUCCESS => "<style='bold fg-green'>" . mb_strtoupper($this->value) . '</style>',
            InsightType::WARNING => "<style='bold fg-yellow'>" . mb_strtoupper($this->value) . '</style>',
            InsightType::NORMAL => $this->value,
        };
    }

    public function __construct(
        private(set) readonly string $value,
        private readonly InsightType $type = InsightType::NORMAL,
    ) {}
}
