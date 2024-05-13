<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Tempest\Console\Components\HasStaticComponent;
use Tempest\Console\Components\InteractiveComponent;
use Tempest\Console\Components\Static\StaticConfirmComponent;
use Tempest\Console\Components\StaticComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\Key;
use Tempest\Console\Point;

final class ConfirmComponent implements InteractiveComponent, HasCursor, HasStaticComponent
{
    private bool $answer;
    private string $textualAnswer = '';

    public function __construct(
        private readonly string $question,
        private readonly bool $default = false,
    ) {
        $this->answer = $default;
    }

    public function render(): string
    {
        return sprintf(
            '%s [%s/%s] %s',
            "<question>{$this->question}</question>",
            $this->answer ? '<em><u>yes</u></em>' : 'yes',
            $this->answer ? 'no' : '<em><u>no</u></em>',
            $this->textualAnswer,
        );
    }

    public function renderFooter(): string
    {
        return 'Press <em>enter</em> to confirm, <em>ctrl+c</em> to cancel';
    }

    #[HandlesKey(Key::DOWN)]
    #[HandlesKey(Key::UP)]
    #[HandlesKey(Key::LEFT)]
    #[HandlesKey(Key::RIGHT)]
    public function toggle(): void
    {
        $this->answer = ! $this->answer;

        if ($this->textualAnswer) {
            $this->textualAnswer = $this->answer ? 'y' : 'n';
        }
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): bool
    {
        return $this->answer;
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        preg_match('/([yn])/i', $key, $matches);

        $answer = $matches[0] ?? null;

        if ($answer !== $key) {
            return;
        }

        $this->textualAnswer = strtolower($answer);

        $this->answer = $this->textualAnswer === 'y';
    }

    public function getCursorPosition(): Point
    {
        return new Point(
            x: strlen($this->question) + 15,
            y: 0,
        );
    }

    public function getStaticComponent(): StaticComponent
    {
        return new StaticConfirmComponent(
            $this->question,
            $this->default,
        );
    }
}
