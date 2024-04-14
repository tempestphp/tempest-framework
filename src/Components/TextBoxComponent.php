<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\ConsoleComponent;
use Tempest\Console\Cursor;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\Key;
use Tempest\Console\Point;

final class TextBoxComponent implements ConsoleComponent, HasCursor
{
    public Point $componentCursorPosition;
    public string $answer = '';

    public function __construct(
        public string $label,
    ) {
        $this->componentCursorPosition = new Point(2, 1);
    }

    public function render(): string
    {
        $output = "<question> {$this->label} </question> {$this->answer}";

        return $output . PHP_EOL . PHP_EOL . "Press <em>enter</em> to confirm, press <em>ctrl+c</em> to cancel" . PHP_EOL;
    }

    #[HandlesKey(Key::BACKSPACE)]
    public function backspace(): void
    {
        $offset = $this->componentCursorPosition->x - 2;

        if ($offset <= 0) {
            return;
        }

        $this->answer = substr($this->answer, 0, $offset - 1) . substr($this->answer, $offset);

        $this->left();
    }

    #[HandlesKey(Key::DELETE)]
    public function delete(): void
    {
        $offset = $this->componentCursorPosition->x - 2;

        $this->answer = substr($this->answer, 0, $offset) . substr($this->answer, $offset + 1);
    }

    #[HandlesKey(Key::ENTER)]
    public function enter(): string
    {
        return $this->answer;
    }

    #[HandlesKey(Key::UP)]
    public function up(): void
    {
        $this->componentCursorPosition->x = 2;
    }

    #[HandlesKey(Key::DOWN)]
    public function down(): void
    {
        $this->componentCursorPosition->x = strlen($this->answer) + 2;
    }

    #[HandlesKey(Key::LEFT)]
    public function left(): void
    {
        $this->componentCursorPosition->x = max(2, $this->componentCursorPosition->x - 1);
    }

    #[HandlesKey(Key::RIGHT)]
    public function right(): void
    {
        $this->componentCursorPosition->x = min(strlen($this->answer) + 2, $this->componentCursorPosition->x + 1);
    }

    #[HandlesKey]
    public function input(string $key): void
    {
        preg_match('/[\w\s]+/', $key, $matches);

        if (($matches[0] ?? null) !== $key) {
            return;
        }

        $this->componentCursorPosition->x += 1;
        $this->answer .= $key;
    }

    public function placeCursor(Cursor $cursor): void
    {
        $cursor->place(new Point(
            x: $cursor->getPosition()->x + $this->componentCursorPosition->x + strlen($this->label) + 1,
            y: $cursor->getPosition()->y - 3,
        ));
    }
}
