<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Stringable;
use Tempest\Console\Components\State;
use Tempest\Console\Terminal\Terminal;
use Tempest\Support\StringHelper;

trait RendersInput
{
    public const MARGIN_X = 2;
    public const PADDING_X = 1;
    public const MARGIN_TOP = 1;

    private StringHelper $frame;
    private Terminal $terminal;
    private State $state;

    private int $maxLineCharacters;
    private string $marginX;
    private string $paddingX;
    private string $leftBorder;

    private function prepareRender(Terminal $terminal, State $state): self
    {
        $this->terminal = $terminal;
        $this->state = $state;

        $this->marginX = str_repeat(' ', self::MARGIN_X);
        $this->paddingX = str_repeat(' ', self::PADDING_X);
        $this->leftBorder = "<style=\"dim {$this->getStyle()}\">│</style>";
        $this->maxLineCharacters = $this->terminal->width - mb_strlen($this->marginX . ' ' . $this->paddingX) - self::MARGIN_X;

        $this->frame = new StringHelper(str_repeat(PHP_EOL, self::MARGIN_TOP));

        return $this;
    }

    private function finishRender(): string
    {
        if ($this->state === State::CANCELLED) {
            $this->frame = $this->frame->append(PHP_EOL, $this->marginX, '<style="fg-red">Cancelled.</style>', PHP_EOL);
        }

        return $this->frame->toString();
    }

    private function truncate(?string $string = null, int $maxLineOffset = 0): string
    {
        if (! $string) {
            return '';
        }

        return (new StringHelper($string))
            ->truncate($this->maxLineCharacters - 1 - $maxLineOffset, end: '…') // -1 is for the ellipsis
            ->toString();
    }

    private function truncateLeft(?string $string = null, int $maxLineOffset = 0): string
    {
        if (! $string) {
            return '';
        }

        $length = $this->maxLineCharacters - 1 - $maxLineOffset;

        if (mb_strwidth($string, 'UTF-8') <= $length) {
            return $string;
        }

        return '…' . strrev(rtrim(mb_strimwidth(strrev($string), 0, $length, encoding: 'UTF-8')));
    }

    private function getStyle(): string
    {
        return match ($this->state) {
            State::CANCELLED => 'fg-red',
            State::ERROR => 'fg-yellow',
            default => 'fg-gray',
        };
    }

    private function centerText(?string $text, int $width, int $padding = 2): string
    {
        $text ??= '';
        $textLength = strlen($text);
        $actualWidth = max($width, $textLength + (2 * $padding));
        $leftPadding = (int) floor(($actualWidth - $textLength) / 2);
        $rightPadding = $actualWidth - $leftPadding - $textLength;

        return str_repeat(' ', $leftPadding) . $text . str_repeat(' ', $rightPadding);
    }

    private function style(?string $style, string|Stringable ...$content): string
    {
        if (! $style) {
            return implode('', $content);
        }

        return "<style=\"$style\">" . implode('', $content) . '</style>';
    }

    private function label(string $label): self
    {
        return $this->line($this->style('bold fg-cyan', $this->truncate($label)), PHP_EOL);
    }

    private function line(string|Stringable ...$append): self
    {
        if (empty($append)) {
            return $this;
        }

        $this->frame = $this->frame
            ->append($this->marginX, $this->leftBorder, $this->paddingX)
            ->append(...$append);

        return $this;
    }

    private function newLine(bool $border = false): self
    {

        if ($border) {
            $this->line(PHP_EOL);
        } else {
            $this->frame = $this->frame->append(PHP_EOL);
        }

        return $this;
    }
}
