<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Highlight\Tokens\TokenType;
use function Tempest\Support\str;

final readonly class DynamicTokenType implements TokenType
{
    public function __construct(
        private string $tag,
        private string $mod,
    ) {
    }

    public function getStyle(): TerminalStyle
    {
        foreach (TerminalStyle::cases() as $case) {
            $styleName = str($case->name)->lower();

            if ($this->tag === 'mod' && $styleName->replace('_', '')->equals($this->mod)) {
                return $case;
            }

            if (! $styleName->startsWith("{$this->tag}_")) {
                continue;
            }

            $mod = $styleName
                ->replaceStart('fg_', '')
                ->replaceStart('bg_', '')
                ->replace('_', '');

            if ($mod->equals($this->mod)) {
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
