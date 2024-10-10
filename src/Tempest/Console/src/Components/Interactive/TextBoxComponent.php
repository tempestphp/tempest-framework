<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Interactive;

use Tempest\Console\Components\Static\StaticTextBoxComponent;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\HasStaticComponent;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Key;
use Tempest\Console\Point;
use Tempest\Console\StaticConsoleComponent;

final class TextBoxComponent implements InteractiveConsoleComponent, HasCursor, HasStaticComponent
{
    public Point $cursorPosition;

    public string $answer = '';

    public function __construct(
        public string $label,
        public ?string $default = null,
    ) {
        $this->cursorPosition = new Point(2, 1);

        foreach (str_split($default ?? '') as $character) {
            $this->input($character);
        }
    }

    public function render(): string
    {
        return "<question>{$this->label}</question> {$this->answer}";
    }

    public function renderFooter(): string
    {
        return "Press <em>enter</em> to confirm, <em>ctrl+c</em> to cancel";
    }

    #[HandlesKey(Key::BACKSPACE)]
    public function backspace(): void
    {
        $offset = $this->cursorPosition->x - 2;

        if ($offset <= 0) {
            return;
        }

        $this->answer = substr($this->answer, 0, $offset - 1) . substr($this->answer, $offset);

        $this->left();
    }

    #[HandlesKey(Key::DELETE)]
    public function delete(): void
    {
        $offset = $this->cursorPosition->x - 2;

        $this->answer = substr($this->answer, 0, $offset) . substr($this->answer, $offset + 1);
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): string
    {
        return $this->answer;
    }

    #[HandlesKey(Key::UP)]
    #[HandlesKey(Key::HOME)]
    public function home(): void
    {
        $this->cursorPosition->x = 2;
    }

    #[HandlesKey(Key::DOWN)]
    #[HandlesKey(Key::END)]
    public function end(): void
    {
        $this->cursorPosition->x = strlen($this->answer) + 2;
    }

    #[HandlesKey(Key::LEFT)]
    public function left(): void
    {
        $this->cursorPosition->x = max(2, $this->cursorPosition->x - 1);
    }

    #[HandlesKey(Key::RIGHT)]
    public function right(): void
    {
        $this->cursorPosition->x = min(strlen($this->answer) + 2, $this->cursorPosition->x + 1);
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        if (str_starts_with($key, "\e")) {
            return;
        }

        $offset = $this->cursorPosition->x - 2;

        $this->answer = substr($this->answer, 0, $offset) . $key . substr($this->answer, $offset);

        $this->right();
    }

    public function getCursorPosition(): Point
    {
        return new Point(
            x: $this->cursorPosition->x + strlen($this->label) + 1,
            y: $this->cursorPosition->y - 1,
        );
    }

    public function getStaticComponent(): StaticConsoleComponent
    {
        return new StaticTextBoxComponent($this->label);
    }
}
