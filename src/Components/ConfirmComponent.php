<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\ConsoleComponent;
use Tempest\Console\Cursor;
use Tempest\Console\HandlesKey;
use Tempest\Console\HasCursor;
use Tempest\Console\Key;

final class ConfirmComponent implements ConsoleComponent, HasCursor
{
    private bool $answer;
    private string $textualAnswer = '';

    public function __construct(
        private string $question,
        bool $default = false,
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
        ) . PHP_EOL . 'Press <em>enter</em> to confirm';
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

    public function placeCursor(Cursor $cursor): void
    {
        $cursor->moveUp()->moveRight(strlen($this->question) + 12 + strlen($this->textualAnswer));
    }
}
