<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Highlight\Tokens\TokenType;
use function Tempest\Support\str;

final readonly class DynamicTokenType implements TokenType
{
    public function __construct(
        private string $style,
    ) {
    }

    public function getStyle(): TerminalStyle
    {
        $normalizedStyle = str($this->style)
            ->lower()
            ->replace(['_', '-'], '');

        foreach (TerminalStyle::cases() as $case) {
            $normalizedCase = str($case->name)
                ->lower()
                ->replace(['_', '-'], '');

            if ($normalizedCase->equals($normalizedStyle)) {
                return $case;
            }
        }

        return TerminalStyle::RESET;
    }

    public function getValue(): string
    {
        return '';
    }

    public function canContain(TokenType $other): bool
    {
        return false;
    }
}
